
    <link href="vendor/Tabulator/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="vendor/Tabulator/dist/js/tabulator.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/modules/format.min.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/jquery.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/javascript2.js"></script>


    <title>lazer</title>



     <div id = "example-table"></div> <br/>
     <p>Местоположение: </p>
     <div id = "location"></div>
     <!-- <div id = "records"></div> -->



        <script type="text/javascript">

            let tabledata = <?php include_once "get_data.php"; ?>;
            let socket;
            let host = "wss://mayak.net:8003";
            socket = new WebSocket(host);
            socket.onopen = function() {
                console.log("Соединение установлено.");
            };
            socket.onclose = function(event) {
                if (event.wasClean) {
                    console.log('Соединение закрыто чисто');
                } else {
                    console.log('Обрыв соединения'); // например, "убит" процесс сервера
                }
                console.log('Код: ' + event.code + ' причина: ' + event.reason);
            };
            socket.onmessage = function(event) {
                console.log("Получены данные " + event.data);
            };



            var table = new Tabulator("#example-table", {
                autoResize : false,
                // width: 600,
                //height:155,
                data:tabledata,
                layout:"fitDataFill",
                responsiveLayout:"collapse",
                // layout:"fitColumns",
                columns:[ //Define Table Columns
                    // {formatter:"responsiveCollapse", width:30, minWidth:30, align:"center", resizable:false, headerSort:false},
                    {title:"id", field:"id", align:"center", width:120, responsive:0},
                    {title:"Номер ОТМ", field:"otm", width:70},
                    {title:"Объект", field:"object",width:150, align:"left"}, //formatter:"progress"
                    {title:"Идентификатор", field:"imei",width:150},
                    {title:"id_onesignal", field:"id_onesignal", width:150},
                    {title:"Начало", field:"start_date", sorter:"date", align:"center", width:150, editor:dateEditor},
                    {title:"Окончание", field:"end_date", sorter:"date", width:150, align:"center"},
                    {title:"Режим работы", field:"work_mode",width:150, align:"center"},
                    {title:"Местоположение", width:250,  align:"center",
                        columns: [
                            {title:"t(min)", field:"location_interval",width:75, align:"center", editor:true, validator:"integer"},
                            {title:"Отправить", formatter:printIcon, width:75, align:"center", cellClick:function(e, cell){
                                var id_sender = cell.getRow()._row.cells[3].value;
                                var loc_interval = cell.getRow()._row.cells[8].value;
                                var work_mode = cell.getRow()._row.cells[7].value;
                                socket.send(id_sender + ':' + loc_interval + work_mode);
                                console.log(id_sender + ':' + loc_interval + work_mode);
                            }},
                            //{title:"latitude", field:"latitude", align:"center"},
             ``               //{title:"longitude", field:"longitude", align:"center"},
                        ],
                    },
                    {title:"Аудиозапись", align:"center",
                        columns: [
                            {title:"t(min)", field:"duration", width:75, align:"center", editor:"input", validator:"numeric"},
                            {title:"Отправить", formatter:printIcon, width:75, align:"center", cellClick:function(e, cell){
                                var qw = cell.getRow()._row.cells[9].value;
                                console.log(qw);
                            }},
                            {title:"Last cmd", field:"start_datetime",width:140, align:"center"},
                        ],
                    },

                ],
                // rowClick:function(e, row){ //trigger an alert message when the row is clicked
                //     alert("Row " + row.getData().id + " Clicked!!!!");
                // },
            });


        //var tabledata = <?php //print json_encode($rows_loc); ?>;
        // var table2 = new Tabulator("#location", {
        //     autoResize : true,
        //     width: 600,
        //     //height:155,
        //     data:tabledata,
        //     layout:"fitDataFill",
        //     responsiveLayout:"collapse",
        //     // layout:"fitColumns", //fit columns to width of table (optional)
        //     columns:[ //Define Table Columns
        //         // {formatter:"responsiveCollapse", width:30, minWidth:30, align:"center", resizable:false, headerSort:false},
        //         {title:"id", field:"id", align:"center", width:120, responsive:0},
        //         {title:"Номер ОТМ", field:"id_otm", width:170},
        //         {title:"latitude", field:"latitude",width:150, align:"left"}, //formatter:"progress"
        //         {title:"longitude", field:"longitude",width:150},
        //         {title:"active", field:"active", sorter:"date", align:"center", width:150, responsive:2},
        //     ],
        //     // rowClick:function(e, row){ //trigger an alert message when the row is clicked
        //     //     alert("Row " + row.getData().id + " Clicked!!!!");
        //     // },
        // });

            // $("#ajax-trigger").click(function(){
            //     alert(1);
            //     table.setData("/exampledata/ajax");
            // });









        </script>
