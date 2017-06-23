<?php

    
    // usefull to decide choose which db connect to

    /* MAKE SURE TO CHANGE DB NAMES IN SEND SMS PROCESSES */
    function connToStagingDb()
    {
        return 1;  
    }

    
    function getProductionDbConn($m)
    {
        return $m->modernDefence;
    }
    function getStagingDbConn($m)
    {
        return $m->modernDefenceStaging;
    }
    function getAuthenticationDbAdminConn($m)
    {
        return $m->modernDefenceAdmin;
    }
    function getProdAutDbUsersConn($m)
    {
        return $m->modernDefenceUsers;
    }
    function getStgAutDbUsersConn($m)
    {
        return $m->staging_modernDefenceUsers;
    }
?>