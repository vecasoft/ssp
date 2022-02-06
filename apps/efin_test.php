<?php

session_start();

// -----
// inits
// -----

if (@$_GET["seid"])
    $_SESSION["SEID"] = @$_GET["seid"];

$seid = $_SESSION["SEID"];

$rootDir = (substr($_SERVER["SCRIPT_FILENAME"], 0, (stripos($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"])+0)));
$_SESSION["SX_ROOTDIR"] = $rootDir;

echo $rootDir;

include_once $rootDir . '/sx.class.php';


include_once(Sx::GetSxClassPath("sessions.class"));
include_once(Sx::GetSxClassPath("tools.class"));
include(Sx::GetSxClassPath("mysql.incl"));	// Creates a $db object
include_once(Sx::GetClassPath("efin.class"));
include_once(SX::GetClassPath("_db.class"));
?>

<!DOCTYPE HTML>
<html>



<?php

    echo '<script type="text/javascript" src="/jquery/jquery.js"> </script>';
    echo '<link rel="stylesheet" href="/jquery/jquery-ui.css" />';
    echo '<link rel="stylesheet" href="/bootstrap/css/bootstrap_extract.css" />';
    echo '<script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"> </script>';

    echo '<link rel="stylesheet" href="/js/jstree/dist/themes/default/style.min.css" />';
    echo '<script src="/js/jstree/dist/jstree.min.js"></script>';

    ?>

<body>
sqdsqds
    <button class="btn btn-success" >Dit is een test</button>

<?php

    $adRec = SSP_db::Get_SSP_adRec('gverhelst');
    echo $adRec->adNaamVoornaam;


?>

<h1>HTML demo</h1>
<div id="html" class="demo">
    <ul>
        <li data-jstree='{ "opened" : true }'>Root node
            <ul>
                <li data-jstree='{ "selected" : true }'>Child node 1</li>
                <li>Child node 2</li>
            </ul>
        </li>
    </ul>
</div>

<h1>Inline data demo</h1>
<div id="data" class="demo"></div>

<h1>Data format demo</h1>
<div id="frmt" class="demo"></div>

<h1>AJAX demo</h1>
<div id="ajax" class="demo"></div>

<h1>Lazy loading demo</h1>
<div id="lazy" class="demo"></div>

<h1>Callback function data demo</h1>
<div id="clbk" class="demo"></div>

<h1>Interaction and events demo</h1>
<button id="evts_button">select node with id 1</button> <em>either click the button or a node in the tree</em>
<div id="evts" class="demo"></div>



<script>
    // html demo
    $('#html').jstree();

    // inline data demo
    $('#data').jstree({
        'core' : {
            'data' : [
                { "text" : "Root node", "children" : [
                        { "text" : "Child node 1" },
                        { "text" : "Child node 2" }
                    ]}
            ]
        }
    });

    // data format demo
    $('#frmt').jstree({
        'core' : {
            'data' : [
                {
                    "text" : "Root node",
                    "state" : { "opened" : true },
                    "children" : [
                        {
                            "text" : "Child node 1",
                            "state" : { "selected" : true },
                            "icon" : "jstree-file"
                        },
                        { "text" : "Child node 2", "state" : { "disabled" : true } }
                    ]
                }
            ]
        }
    });

    // ajax demo
    $('#ajax').jstree({
        'core' : {
            'data' : {
                "url" : "./root.json",
                "dataType" : "json" // needed only if you do not supply JSON headers
            }
        }
    });

    // lazy demo
    $('#lazy').jstree({
        'core' : {
            'data' : {
                "url" : "//www.jstree.com/fiddle/?lazy",
                "data" : function (node) {
                    return { "id" : node.id };
                }
            }
        }
    });

    // data from callback
    $('#clbk').jstree({
        'core' : {
            'data' : function (node, cb) {
                if(node.id === "#") {
                    cb([{"text" : "Root", "id" : "1", "children" : true}]);
                }
                else {
                    cb(["Child"]);
                }
            }
        }
    });

    // interaction and events
    $('#evts_button').on("click", function () {
        var instance = $('#evts').jstree(true);
        instance.deselect_all();
        instance.select_node('1');
    });
    $('#evts')
        .on("changed.jstree", function (e, data) {
            if(data.selected.length) {
                alert('The selected node is: ' + data.instance.get_node(data.selected[0]).text);
            }
        })
        .jstree({
            'core' : {
                'multiple' : false,
                'data' : [
                    { "text" : "Root node", "children" : [
                            { "text" : "Child node 1", "id" : 1 },
                            { "text" : "Child node 2" }
                        ]}
                ]
            }
        });
</script>


</body>
</html>