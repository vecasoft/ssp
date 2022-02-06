<?php

session_start();

// -----
// inits
// -----

if (! $_SESSION["SX_BASEPATH"]) {
    $rootDir = (substr($_SERVER["SCRIPT_FILENAME"], 0, (stripos($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"]) + 0)));
    $_SESSION["SX_BASEPATH"] = $rootDir . "\"";
}

Include_once $_SESSION["SX_BASEPATH"] . '/sx.class.php';

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
    echo '<link rel="stylesheet" href="/bootstrap/css/bootstrap.css" />';
    echo '<script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"> </script>';

    echo '<link rel="stylesheet" href="/js/jstree/dist/themes/default/style.min.css" />';
echo '<link rel="stylesheet" href="/js/jstree/dist/themes/proton/style.min.css" />';
    echo '<script src="/js/jstree/dist/jstree.min.js"></script>';

    ?>

<body>

<button class="btn btn-success"  onclick="hdlTree()"><span class="glyphicon glyphicon-save"></span> Bewaren</button>

    <form class="form-horizontal">
        <div class="input-group col-sm-12" style="margin-bottom: 10px">

        </div>
        <div class="input-group col-sm-12">
            <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
            <input id="search" type="text" class="form-control search-input" name="search" placeholder="Zoek">
        </div>
    </form>

    <div id="tree-container"></div>

<script type="text/javascript">
    $(document).ready(function(){
        //fill data to tree  with AJAX call


        $('#tree-container').jstree({
            'plugins': ["themes","wholerow", "checkbox", "dnd", "search", "contextmenu", "state", "types"],
            'search': {
                "case_sensitive": false,
                "show_only_matches": false
            },
            'core' : {
                'check_callback' : function (operation, node, node_parent, node_position, more) {
                    return true;
                },


                'data' : {
                    "url" : "efin_analytische_structuur_data.php",
                    "plugins" : [ "wholerow", "checkbox" ],
                    "dataType" : "json" // needed only if you do not supply JSON headers
                },

                'themes': {
                    'name': 'default',
                    'icons' : true,
                    // 'responsive': true
                }

            },
            "types" : {
                    "default" : {
                        // "icon" : "glyphicon glyphicon-list",
                        "max_depth": 2
                    },
            }

        })

        $(".search-input").keyup(function () {
            var searchString = $(this).val();
            $('#tree-container').jstree('search', searchString);
        });

    });

    function hdlTree(){

        var v =$("#tree-container").jstree(true).get_json('#', {'flat': true});

        var dataString = JSON.stringify(v);

        $.ajax({
            url: 'efin_analytische_structuur_handle.php',
            data: {myData: dataString},
            type: 'POST',
            success: function(response) {
                alert(response);
            }
        });



    }


</script>



</body>
</html>