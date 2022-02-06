<html lang='en'>
<head>
    <meta charset='utf-8' />

    <link href='fullcalendar/core/main.css' rel='stylesheet' />
    <link href='fullcalendar/daygrid/main.css' rel='stylesheet' />
    <link href='fullcalendar/bootstrap/main.css' rel='stylesheet' />
    <link href='fullcalendar/list/main.css' rel='stylesheet' />

    <script src='fullcalendar/core/main.js'></script>
    <script src='fullcalendar/daygrid/main.js'></script>
    <script src='fullcalendar/bootstrap/main.js'></script>
    <script src='fullcalendar/list/main.js'></script>
    <script src='fullcalendar/locale/nl.js'></script>

</head>
<body>

<div class="container">
<div class="row">
<div class="col">

    <?php

    $sqlStat = "Select * from sx_ch_calendar_headers where chCalendar = '$calendar'";
    $db->Query($sqlStat);

    if ($chRec = $db->Row()){

        if ($chRec->chName)
            echo "<h1>Kalender - $chRec->chName</h1>";

        if ($chRec->chDescription){

            echo "<div class='alert alert-success' style='margin-top: 10px; padding-top: 10px; padding-bottom: 10px'>";
            echo $chRec->chDescription;
            echo "</div>";

        }



    }



    ?>



    <div id='calendar'></div>


</div></div></div>


<script>

    document.addEventListener('DOMContentLoaded', function() {

        var calendarEl = document.getElementById('calendar');

        function mobileCheck() {
            if (window.innerWidth >= 768 ) {
                return false;
            } else {
                return true;
            }
        };

        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'listPlugin', 'dayGrid', 'timeGrid', 'list', 'bootstrap' ],
            timeZone: 'UTC',
            themeSystem: 'bootstrap',
            header: {
                left: 'prev,next today',
                center: 'title',
                //right: 'listWeek,listMonth'
                right: ''
            },
            defaultView: mobileCheck() ? "listMonth" : "dayGridMonth",
            windowResize: function(view) {
                if (window.innerWidth >= 768 ) {
                    calendar.changeView('dayGridMonth');
                } else {
                    calendar.changeView('listMonth');
                }
            },
            eventRender: function(info) {
                var start = info.event.start;
                var end = info.event.end;
                var startTime;
                var endTime;

                if (!start) {
                    startTime = '';
                } else {
                    startTime = start;
                }

                if (!end) {
                    endDate = '';
                } else {
                    endTime = end;
                }

                var title = info.event.title;
                var omschrijving2 = info.event.extendedProps.omschrijving2;
                if (!info.event.extendedProps.omschrijving2) {
                    omschrijving2 = '';
                }

                $(info.el).popover({
                    title: title,
                    placement:'top',
                    trigger : 'click',
                    content: omschrijving2,
                    container:'body'
                }).popover('show');
            },
            weekNumbers: true,
            firstDay:1,
            locale: 'nl',
            lang: 'nl',
            buttonText: {
                today:    'Vandaag',
                month:    'Maand',
                week:     'week',
                day:      'Dag'
            },
            lang: 'nl',
            eventLimit: true, // allow "more" link when too many events
            eventSources: [

                // your event source
                {
                    url: '/calendarFeed.php?calendar=<?php echo $calendar ?>', // use the `url` property

                }
            ]
        });

        calendar.render();
    });

</script>



<script>
    $('[data-toggle="popover"]').popover();
</script>

</body>
</html>