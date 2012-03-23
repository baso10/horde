<?php
/**
 * Horde_ActiveSync_Message_Flag class represents a single email flag.
 *
 * @copyright 2012 Horde LLC (http://www.horde.org)
 * @author Michael J Rubinsky <mrubinsk@horde.org>
 * @package ActiveSync
 */
class Horde_ActiveSync_Message_Flag extends Horde_ActiveSync_Message_Base
{
    var $flagstatus;
    var $flagtype;
    var $completetime;
    const POOMMAIL_FLAGSTATUS        = 'POOMMAIL:FlagStatus';
    const POOMMAIL_FLAGTYPE          = 'POOMMAIL:FlagType';
    const POOMMAIL_COMPLETETIME      = 'POOMMAIL:CompleteTime';

    protected $_mapping = array(
        self::POOMMAIL_FLAGSTATUS      => array(self::KEY_ATTRIBUTE => 'flagstatus'),
        self::POOMMAIL_FLAGTYPE        => array(self::KEY_ATTRIBUTE => 'flagtype'),
        Horde_ActiveSync_Message_Task::POOMTASKS_STARTDATE      => array(self::KEY_ATTRIBUTE => 'startdate', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_UTCSTARTDATE   => array(self::KEY_ATTRIBUTE => 'utcstartdate', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_DUEDATE        => array(self::KEY_ATTRIBUTE => 'duedate', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_UTCDUEDATE     => array(self::KEY_ATTRIBUTE => 'utcduedate', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_DATECOMPLETED  => array(self::KEY_ATTRIBUTE => 'datecompleted', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_REMINDERSET    => array(self::KEY_ATTRIBUTE => 'reminderset'),
        Horde_ActiveSync_Message_Task::POOMTASKS_REMINDERTIME   => array(self::KEY_ATTRIBUTE => 'remindertime', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_SUBJECT        => array(self::KEY_ATTRIBUTE => 'subject'),
        Horde_ActiveSync_Message_Task::POOMTASKS_ORDINALDATE    => array(self::KEY_ATTRIBUTE => 'ordinaldate', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        Horde_ActiveSync_Message_Task::POOMTASKS_SUBORDINALDATE => array(self::KEY_ATTRIBUTE => 'subordinaldate', self::KEY_TYPE => self::TYPE_DATE_DASHES),
        self::POOMMAIL_COMPLETETIME    => array(self::KEY_ATTRIBUTE => 'completetime'),
    );

    protected $_properties = array(
        'flagstatus' => false,
        'flagtype' => false,
        'startdate' => false,
        'utcstartdate' => false,
        'duedate' => false,
        'utcduedate' => false,
        'datecompleted' => false,
        'reminderset' => false,
        'remindertime' => false,
        'subject' => false,
        'ordinaldate' => false,
        'subordinaldate' => false,
        'completetime' => false,
    );

    public function getClass()
    {
        return 'Flag';
    }

}