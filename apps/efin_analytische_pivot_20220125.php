<?php

session_start();

$_SESSION["SX_BASEPATH"] = $_SERVER['DOCUMENT_ROOT'];

// --------------
// Get session-id
// --------------

if (isset($_GET['seid']))  {
    $seid = $_GET['seid'];
    $_SESSION["SEID"] = $seid;
}
else
    $seid = $_SESSION["SEID"];

// -----
// inits
// -----

Include_once $_SERVER['DOCUMENT_ROOT'] . '/sx.class.php';

include_once(Sx::GetSxClassPath("auth.class"));
include_once(Sx::GetSxClassPath("sessions.class"));
include_once(Sx::GetSxClassPath("tools.class"));
include_once(Sx::GetSxClassPath("apps.class"));
include(Sx::GetSxClassPath("mysql.incl"));    // Creates a $db object
include_once(Sx::GetClassPath("efin.class"));
include_once(Sx::GetClassPath("efin_report.class"));
include_once(SX::GetClassPath("_db.class"));

// ---------------
// Check authority
// ---------------

$userId = SX_sessions::GetSessionUserId($seid);
$appCode = 'efin_analytische_pivot';

$auth = SX_auth::CheckUserAuth($userId, $appCode);

if($auth != '*OK')
    $auth = SX_auth::CheckUserAuth($userId, 'efin_admin');

if($auth != '*OK')
    die("Geen autoriteit tot deze toepassing...");

if ($_SESSION["appCode"] != $appCode) {
    $_SESSION["appCode"] = $appCode;
    SX_apps::LogAppUsage($appCode, $userId , $seid);
}

?>

<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">

<link href="https://cdn.webdatarocks.com/latest/webdatarocks.min.css" rel="stylesheet"/>
<script src="https://cdn.webdatarocks.com/latest/webdatarocks.toolbar.min.js"></script>
<script src="https://cdn.webdatarocks.com/latest/webdatarocks.js"></script>


<body>


<div id="wdr-component"></div>


<script>
    var pivot = new WebDataRocks({
        container: "wdr-component",
        beforetoolbarcreated: customizeToolbar,
        toolbar: true,
        customizeCell: customizeCellFunction,
        global: {
            // replace this path with the path to your own translated file
            localization: "/js/webdatarocks/nl.json"
        },
        report: {
            dataSource: {
                data: getJSONData()
            },
            formats: [{
                name: "bedrag",
                maxDecimalPlaces: 2,
                maxSymbols: 20,
                textAlign: "right",
                "currencySymbol": "€ ",
                "currencySymbolAlign": "left",
                "thousandsSeparator": ".",
                "decimalSeparator": ",",
            },
                {
                    name: "text",
                    textAlign: "left",

                }
            ],
            slice: {
                "reportFilters": [{
                    "uniqueName": "Analytische Rekening"
                }],
                rows: [{
                    uniqueName: "Analytische Rekening"
                }],
                columns: [{
                    uniqueName: "measures"
                }],
                measures: [{
                    uniqueName: "bedrag_2022",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    uniqueName: "budget_2022",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    uniqueName: "bedrag_2021",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    uniqueName: "bedrag_2020",
                    aggregation: "sum",
                    format: "bedrag"
                },

                {
                    "uniqueName": "detail",
                    "formula": "0+0",
                    "individual": false,
                    "caption": "EFIN",
                    format: "text"

                }


                ]
            }
        }
    });

    function customizeCellFunction(cell, data) {

        $kol = data.columnIndex;
        $row = data.rowIndex;

        var value = webdatarocks.getCell(0, $kol);
        $header =  value.label;

        var value = webdatarocks.getCell($row, 0);
        $rekening =  value.label;

<?php
       echo "var seid = '";
       echo $seid;
       echo "';";
?>
        if ($header == "EFIN" && ! data.isGrandTotal)

            cell.text = "<a href='/efin_analytische_verrichtingen.php?seid=" + seid + "&rekening=" + $rekening + "' target='_blank'>KLIK HIER</a>";

        if ($header == "EFIN" && data.isGrandTotal && $row > 0)
            cell.text = "";
    }

    function customizeToolbar(toolbar) {
        var tabs = toolbar.getTabs(); // get all tabs from the toolbar

        toolbar.getTabs = function() {

            delete tabs[0];
            delete tabs[1];
            delete tabs[2];
            delete tabs[3];
            delete tabs[4];
            delete tabs[6];

            tabs.unshift({
                id: "exportNaarXLS",
                title: "XLS",
                handler: newtabHandlerXLS,
                icon: this.icons.export
            });
            return tabs;
        }
        var newtabHandlerXLS = function() {
            webdatarocks.exportTo("excel");
        };

    }

    function getJSONData() {

        <?php

        $json = SSP_efin_report::GetAnalytischeStructuurJSON();
        echo "return $json";

        ?>

    }

</script>


</body>
</html>