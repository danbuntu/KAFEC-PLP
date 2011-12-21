<?php

    /**
     * Cron script fires the MoodletxtCronHandler object
     * to update/maintain moodletxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2010070712
     * @since 2006101012
     */

    // Get config and required libraries
    require_once('../../config.php');
    require_once($CFG->dirroot . '/blocks/moodletxt/db/MoodletxtCronHandler.php');

    $cron = new MoodletxtCronHandler();
    $cron->doCron();

    die();

?>
