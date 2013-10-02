<?php
/**
 * Copyright 2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @category  Horde
 * @copyright 2013 Horde LLC
 * @license   http://www.fsf.org/copyleft/gpl.html GPL
 * @package   IMP
 */

/**
 * Generate image for an unknown contact.
 *
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2013 Horde LLC
 * @license   http://www.horde.org/licenses/gpl GPL
 * @package   IMP
 */
class IMP_Contacts_Image_Unknown implements IMP_Contacts_Image_Backend
{
    /**
     */
    public function rawImage($email)
    {
        return null;
    }

    /**
     */
    public function urlImage($email)
    {
        return Horde_Themes::img('unknown_contact.jpg');
    }

}
