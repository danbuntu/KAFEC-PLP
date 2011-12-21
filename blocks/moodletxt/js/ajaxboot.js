<!--
//<![CDATA[[

    /**
     * Javascript used to boot up an XMLHttpRequest
     * object (or its equivalent) for use in an AJAX app
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @version 2007082112
     * @since 2007082112
     */

    var xmlhttp = false;

    // Detect Internet Exploder
    try {

       xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");

    } catch (ex1) {

        try {

            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");

        } catch (ex2) {

            xmlhttp = false;

        }

    }

    // Detect proper browsers
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {

        try {

            xmlhttp = new XMLHttpRequest();

        } catch (e) {

            xmlhttp=false;

        }

    }

    // IceBrowser, anyone?
    if (!xmlhttp && window.createRequest) {

        try {

            xmlhttp = window.createRequest();

        } catch (e) {

            xmlhttp=false;

        }

    }


//]]>
//-->
