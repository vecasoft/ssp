<?php

session_start();

// -------
// Classes
// -------


if ($_SESSION["SX_BASEPATH"] <= " ") {

    $rootDir = (substr($_SERVER["SCRIPT_FILENAME"], 0, (stripos($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"])+0)));
    $_SESSION["SX_BASEDIR"] = dirname($_SERVER["SCRIPT_NAME"]);
    $_SESSION["SX_BASEDIR"] = '';

    $_SESSION["SX_BASEPATH"] = $rootDir . dirname ($_SERVER["SCRIPT_NAME"]);
    $_SESSION["SX_ROOTDIR"] = $rootDir;

}

include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';
include(SX::GetSxClassPath("mysql.incl"));	// Creates a $db object

include_once(SX::GetSxClassPath("tools.class"));
include_once(SX::GetSxClassPath("content.class"));
include_once(SX::GetSxClassPath("sessions.class"));
include_once(SX::GetClassPath("efin_report.class"));

// --------------
// Get session-id
// --------------

if (isset($_GET['seid']))  {
    $seid = $_GET['seid'];
    $_SESSION["SEID"] = $seid;
}
else
    $seid = $_SESSION["SEID"];

// -----------
// Get User-id
// -----------

if ($seid == "veca")
    $userId = 'gverhelst';
else
    $userId = SX_sessions::GetSessionUserId($seid);

if ($userId == '*NONE') {
    error_log("DIE");
    die('<h1>Security Error...</h1>');
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
                data: getJSONData2()
            },
            formats: [{
                name: "bedrag_2020",
                maxDecimalPlaces: 2,
                maxSymbols: 20,
                textAlign: "right",
                "currencySymbol": "€ ",
                "currencySymbolAlign": "left",
                "thousandsSeparator": " ",
                "decimalSeparator": "",
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
                    uniqueName: "bedrag_2020",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    uniqueName: "budget_2020",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    uniqueName: "bedrag_2019",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    "uniqueName": "detail",
                    "formula": "0+0",
                    "individual": false,
                    "caption": "Detail",
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

           if ($header == "Detail" && ! data.isGrandTotal)
                cell.text = "<a href='http://google.com' target='_blank'>LINK naar " + $rekening + "</a>";

        if ($header == "Detail" && data.isGrandTotal && $row > 0)
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


    function getJSONData2() {
        return [{
            "RekeningL1": {
                type: "level",
                hierarchy: "Analytische Rekening"
            },
            "RekeningL2": {
                type: "level",
                hierarchy: "Analytische Rekening",
                level: "Detail",
                parent: "RekeningL1",
            },
            "RekeningL3": {
                type: "level",
                hierarchy: "Analytische Rekening",
                level: "Detail level 2",
                parent: "Detail"
            },
            "bedrag_2020": {
                type: "number",
                caption:"Bedrag [2020]"
            },
            "budget_2020": {
                type: "number",
                caption:"Budget [2020]"
            },
            "bedrag_2019": {
                type: "number",
                caption:"Bedrag [2019]"
            },
        },
            {
                "RekeningL1": "1. Comm & Sponsoring",
                "RekeningL2": "1.1 Sponsoring",
                "RekeningL3": "1.1.1 Sponsorcontracten Voetbal (algemeen)",
                "bedrag_2020": 15123,
                "budget_2020": 12000,
                "bedrag_2019": 12000,
            },
            {
                "RekeningL1": "1. Comm & Sponsoring",
                "RekeningL2": "1.1 Sponsoring",
                "RekeningL3": "1.1.2 Sponsorcontracten GTEAM (specifiek)",
                "bedrag_2020": 1000,
                "budget_2020": 0,
                "bedrag_2019": 0
            },
            {
                "RekeningL1": "1. Comm & Sponsoring",
                "RekeningL2": "1.2 Werkingskosten communicatie",
                "RekeningL3": "1.2.1 Diverse kantoormaterialen",
                "bedrag_2020": -1500,
                "budget_2020": 0,
                "bedrag_2019": 0
            },
             {
                "RekeningL1": "A. Lidgelden voetbal",
                "RekeningL2": "A.1 Lidgelden Seniors",
                "bedrag_2020": 305,
                 "budget_2020": 0,
                 "bedrag_2019": 0

            },
            {
                "RekeningL1": "A. Lidgelden voetbal",
                "RekeningL2": "A.2 Lidgelden Jeugd",
                "RekeningL3": "A.2.* Lidgelden Jeugd",
                "bedrag_2020": 100,
                "budget_2020": 0,
                "bedrag_2019": 0
            },
            {
                "RekeningL1": "A. Lidgelden voetbal",
                "RekeningL2": "A.2 Lidgelden Jeugd",
                "RekeningL3": "A.2.1 Lidgelden Jeugd U6",
                "bedrag_2020": 6000,
                "budget_2020": 0,
                "bedrag_2019": 0
            },
            {
                "RekeningL1": "A. Lidgelden voetbal",
                "RekeningL2": "A.2 Lidgelden Jeugd",
                "RekeningL3": "A.2.1 Lidgelden Jeugd U7",
                "bedrag_2020": 7000,
                "budget_2020": 0,
                "bedrag_2019": 0
            }
        ];
    }
</script>


</body>
</html>