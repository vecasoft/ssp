<?php

session_start();

// -----
// inits
// -----

if (false) {

    if (!$_SESSION["SX_BASEPATH"]) {
        $rootDir = (substr($_SERVER["SCRIPT_FILENAME"], 0, (stripos($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"]) + 0)));
        $_SESSION["SX_BASEPATH"] = $rootDir . "\"";
    }

    Include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';

    include_once(Sx::GetSxClassPath("sessions.class"));
    include_once(Sx::GetSxClassPath("tools.class"));
    include(Sx::GetSxClassPath("mysql.incl"));    // Creates a $db object
    include_once(Sx::GetClassPath("efin.class"));
    include_once(SX::GetClassPath("_db.class"));

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
                    uniqueName: "bedrag",
                    aggregation: "sum",
                    format: "bedrag"
                },
                {
                    uniqueName: "budget",
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
            "bedrag": {
                type: "number",
                caption:"Bedrag"
            },
            "budget": {
                type: "number"
            }

        },
            {
                "RekeningL1": "1. Comm & Sponsoring",
                "RekeningL2": "1.1 Sponsoring",
                "RekeningL3": "1.1.1 Sponsorcontracten Voetbal (algemeen)",
                "bedrag": 15000,
                "budget": 12000,
                "url": "abc"
            },
            {
                "RekeningL1": "1. Comm & Sponsoring",
                "RekeningL2": "1.1 Sponsoring",
                "RekeningL3": "1.1.2 Sponsorcontracten GTEAM (specifiek)",
                "bedrag": 1000,
                "budget": 0
            },
            {
                "RekeningL1": "1. Comm & Sponsoring",
                "RekeningL2": "1.2 Werkingskosten communicatie",
                "RekeningL3": "1.2.1 Diverse kantoormaterialen",
                "bedrag": -1500
            },
             {
                "RekeningL1": "A. Lidgelden voetbal",
                "RekeningL2": "A.1 Lidgelden Seniors",
                "bedrag": 305,
                "budget": ""

            },
            {
                "RekeningL1": "A. Lidgelden voetbal",
                "RekeningL2": "A.2 Lidgelden Jeugd",
                "bedrag": 100000,
                "budget": 125000
            }
        ];
    }
</script>


</body>
</html>