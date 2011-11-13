<?php
/**
 * Base class for loading, parsing, and working with timezones.
 *
 * This class is the central point to fetch timezone information from
 * the timezone (Olson) database, parse it, cached it, and generate
 * VTIMEZONE objects.
 *
 * Usage:
 * <code>
 * $tz = new Horde_Timezone();
 * $tz->getZone('America/New_York')->toVtimezone()->exportVcalendar();
 * </code>
 *
 * Copyright 2011 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @todo Implement caching
 *
 * @author  Jan Schneider <jan@horde.org>
 * @package Timezone
 */
class Horde_Timezone
{
    /**
     * Any configuration parameters for this class.
     *
     * @var array
     */
    protected $_params;

    /**
     * File location of the downloaded timezone database.
     *
     * @var string
     */
    protected $_tmpfile;

    /**
     * List of all Zone entries parsed into Horde_Timezone_Zone objects.
     *
     * @var array
     */
    protected $_zones = array();

    /**
     * List of all Rule entries parsed into Horde_Timezone_Rule objects.
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * Alias map of all Link entries.
     *
     * @var array
     */
    protected $_links = array();

    /**
     * List to map month descriptions used in the timezone database.
     *
     * @var array
     */
    static protected $_months = array('Jan' => 1,
                                      'Feb' => 2,
                                      'Mar' => 3,
                                      'Apr' => 4,
                                      'May' => 5,
                                      'Jun' => 6,
                                      'Jul' => 7,
                                      'Aug' => 8,
                                      'Sep' => 9,
                                      'Oct' => 10,
                                      'Nov' => 11,
                                      'Dec' => 12);

    /**
     * Constructor.
     *
     * @param array $params  List of class parameters. Possible options:
     *                       - location: (string) Location of the timezone
     *                         database, defaults to
     *                         ftp.iana.org/tz/tzdata-latest.tar.gz.
     *                       - client: (Horde_Http_Client) A preconfigured
     *                         HTTP client for downloading via HTTP.
     */
    public function __construct(array $params = array())
    {
        $this->_params = array_merge(
            array('location' => 'ftp://ftp.iana.org/tz/tzdata-latest.tar.gz'),
            $params);
    }

    /**
     * Returns the month number of a month name.
     *
     * @param string $month  A month name.
     *
     * @return integer  The month's number.
     */
    static public function getMonth($month)
    {
        return self::$_months[substr($month, 0, 3)];
    }

    /**
     * Returns an object representing an invidual timezone.
     *
     * Maps to a "Zone" entry in the timezone database. Works with
     * zone aliases too.
     *
     * @param string $zone  A timezone name.
     *
     * @return Horde_Timezone_Zone  A timezone object.
     */
    public function getZone($zone)
    {
        if (!$this->_zones) {
            $this->_extractAndParse();
        }
        if (isset($this->_links[$zone])) {
            $zone = $this->_links[$zone];
        }
        if (!isset($this->_zones[$zone])) {
            throw new Horde_Timezone_Exception(sprintf('Timezone %s not found', $zone));
        }
        return $this->_zones[$zone];
    }

    /**
     * Returns an object representing a set of named transition rules.
     *
     * Maps to a list Rule entries of the same name in the timezone database.
     *
     * @param string $rule  A rule name.
     *
     * @return Horde_Timezone_Rule  A rule object.
     */
    public function getRule($rule)
    {
        if (!$this->_rules) {
            $this->_extractAndParse();
        }
        if (!isset($this->_rules[$rule])) {
            throw new Horde_Timezone_Exception(sprintf('Timezone rule %s not found', $rule));
        }
        return $this->_rules[$rule];
    }

    /**
     * Downloads a timezone database.
     *
     * @throws Horde_Timezone_Exception if downloading fails.
     */
    protected function _download()
    {
        $url = @parse_url($this->_params['location']);
        if (!isset($url['scheme'])) {
            throw new Horde_Timezone_Exception('"location" parameter is missing an URL scheme.');
        }
        if (!in_array($url['scheme'], array('http', 'ftp', 'file'))) {
            throw new Horde_Timezone_Exception(sprintf('Unsupported URL scheme "%s"', $url['scheme']));
        }
        if ($url['scheme'] == 'http') {
            if (isset($this->_params['client'])) {
                $client = $this->_params['client'];
            } else {
                $client = new Horde_Http_Client();
            }
            $response = $client->get($this->_params['location']);
            $this->_tmpfile = Horde_Util::getTempFile();
            stream_copy_to_stream($response->getStream(), fopen($this->_tmpfile, 'w'));
            return;
        }
        try { 
            if ($url['scheme'] == 'ftp') {
                $vfs = new Horde_Vfs_Ftp(array('hostspec' => $url['host'],
                                               'username' => 'anonymous',
                                               'password' => 'anonymous'));
            } else {
                $vfs = new Horde_Vfs_File();
            }
            $this->_tmpfile = $vfs->readFile(dirname($url['path']),
                                             basename($url['path']));
        } catch (Horde_Vfs_Exception $e) {
            throw new Horde_Timezone_Exception($e);
        }
    }

    /**
     * Unpacks the downloaded timezone database and parses all files.
     */
    protected function _extractAndParse()
    {
        if (!$this->_tmpfile) {
            $this->_download();
        }
        $tar = new Archive_Tar($this->_tmpfile);
        foreach ($tar->listContent() as $file) {
            if ($file['typeflag'] != 0) {
                continue;
            }
            $this->_parse($tar->extractInString($file['filename']));
        }
    }

    /**
     * Parses a file from the timezone database.
     *
     * @param string $file  A file location.
     */
    protected function _parse($file)
    {
        $stream = new Horde_Support_StringStream($file);
        $fp = $stream->fopen();
        $zone = null;
        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            if (!strlen($line) || $line[0] == '#') {
                continue;
            }
            $column = preg_split('/\s+/', preg_replace('/#.*$/', '', $line));
            switch ($column[0]) {
            case 'Rule':
                if (!isset($this->_rules[$column[1]])) {
                    $this->_rules[$column[1]] = new Horde_Timezone_Rule($column[1]);
                }
                $this->_rules[$column[1]]->add($column);
                $zone = null;
                break;

            case 'Link':
                $this->_links[$column[2]] = $column[1];
                $zone = null;
                break;

            case 'Zone':
                $zone = $column[1];
                $this->_zones[$zone] = new Horde_Timezone_Zone($zone, $this);
                array_splice($column, 0, 2);
                // Fall through.

            default:
                $this->_zones[$zone]->add($column);
                break;
            }
        }
    }
}