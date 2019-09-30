
    <link href="vendor/Tabulator/dist/css/tabulator.min.css" rel="stylesheet">
<!--    <link href="vendor/Tabulator/dist/css/tabulator_simple.min.css" rel="stylesheet">-->
<!--    <link href="vendor/Tabulator/dist/css/bulma/tabulator_bulma.css" rel="stylesheet">-->
    <link rel="stylesheet" href="js/leaflet.css">
    <script type="text/javascript" src="vendor/Tabulator/dist/js/javascript2.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/tabulator.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/modules/format.min.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/jquery.js"></script>
    <script type="text/javascript" src="js/leaflet.js"></script>
    <style>
        #mapid { height: 620px;

        }
    </style>

    <title>laser</title>

    <div id = "example-table"></div> <br/>

    <p>Местоположение: </p>
    <div id="mapid"></div>





    <script type="text/javascript">

        let tabledata = <?php include_once "get_data.php"; ?>;
        let host = "wss://lazer.test:8003";

        let mymap = L.map('mapid').setView([53.8981064, 27.5449547], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy',
            maxZoom: 18
        }).addTo(mymap);

        let myIcon = L.icon({
            iconUrl: 'js/images/blue_marker.png',
            iconSize: [38, 65],
            iconAnchor: [22, 74],
            popupAnchor: [-3, -76],
            shadowUrl: 'js/images/marker-shadow.png',
            shadowSize: [68, 75],
            shadowAnchor: [22, 74]
        });


        function start(host) {
            let socket = new WebSocket(host);

            socket.onopen = function () {
                console.log("Соединение установлено.");
            };
            socket.onclose = function (event) {
                if (event.wasClean) {
                    console.log('Соединение закрыто чисто');
                } else {
                    console.log('Обрыв соединения'); // например, "убит" процесс сервера
                };
                setTimeout(function() {start(host)}, 5000);
                console.log('Код: ' + event.code + ' причина: ' + event.reason);
            };
            socket.onmessage = function (event) {
                if (IsJsonString(event.data))
                {
                    let data = event.data;
                    //console.log(geojsonData.type);
                    if(data.type == 'Feature') {
                        let myLayer = L.geoJSON().addTo(mymap);
                        console.log('Feature');
                    } else if (data.type =='LineString') {
                        console.log('LineString');
                    }
                } else {
                    console.log(event.data);
                    return;
                }

            };
        };

        start(host, arr = '');

        let Layer = {};
        let layerGroup;




        function button_send (){ //plain text value
            return '<input type="button" value="send">';
        };
        function button_clear (){ //plain text value
            return '<input type="button" value="clear">';
        };
        // let layerGroup;
        let table = new Tabulator("#example-table", {
            autoResize : false,
            // width: 600,
            //height:155,
            //selectable: 1,
            // selectableCheck: function (row){ console.log(row.getData())},
            data:tabledata,
            layout:"fitDataFill",
            //responsiveLayout:"collapse",
            // layout:"fitColumns",
            columns:[ //Define Table Columns
                // {formatter:"responsiveCollapse", width:30, minWidth:30, align:"center", resizable:false, headerSort:false},
                {title:"id", field:"id", align:"center", width:56, responsive:0},
                {title:"#otm", field:"otm", width:70},
                {title:"Object", field:"object",width:100, align:"left"}, //formatter:"progress"
                {title:"imei", field:"imei",width:130},
                {title:"Start", field:"start_date", align:"center", width:140},
                {title:"End", field:"end_date", sorter:"date", width:140, align:"center"},
                {title:"wm", field:"work_mode",width:56, align:"center"},
                // {title:"Аудиозапись", align:"center",
                //     columns: [
                //         {title:"t(min)", field:"duration", width:75, align:"center", editor:"input", validator:"numeric"},
                //         // {title:"Отправить", formatter:printIcon, width:75, align:"center", cellClick:function(e, cell){
                //         //     send_data(cell);
                //         // }},
                //         {title:"Last cmd", field:"start_datetime",width:140, align:"center"},
                //     ],
                // },
                {title:"t(min)", field:"location_interval",width:75, align:"center", editor:true, validator:"integer",},
                {title: "send_to_socket", formatter:button_send, field:"example",align:"center", cellClick:function(e, cell){
                        //let row = cell.getRow();
                        let imei = cell.getData().imei;
                        // console.log(imei);
                        window.socket.send(imei);
                    }
                },
                {title: "active", field:"example",align:"center", formatter:"tickCross", cellClick:function(e, cell){
                        let row = cell.getRow();
                        let id = row.getIndex();
                        let arr = {};
                        arr.imei = cell.getData().imei;
                        arr.sender = 'web';
                        if (cell.getValue() == null) {
                            arr.action = 'insert';
                            cell.setValue(1);
                            // row.select(id);
                            //console.log(arr);
                            send_data('server.php', 'POST', arr);
                        } else {
                            cell.setValue(null);
                            arr.action = 'delete';
                            // row.deselect(id);
                            send_data('server.php', 'POST', arr);
                        }


                    }
                },
                {title:"Местоположение", width:550,  align:"center",
                    columns: [


                        {title:"c", field:"date1", align:"center",width:140, editor:dateEditor},
                        {title:"по", field:"date2", align:"center",width:140, editor:dateEditor},
                        {title:"layer", field:"layer", align:"center", visible: false },
                        {title:"get", width:65,formatter:button_send, align:"center", cellClick:function(e, cell){
                                let row = cell.getRow();
                                let rowIndex = row.getIndex();
                                let layer = row.getCell('layer');
                                layer.setValue('Layer_'+ rowIndex);
                                let arr = {};
                                arr.imei = cell.getData().imei;
                                arr.sender = 'web';
                                arr.date_start = cell.getData().date1;
                                arr.date_end = cell.getData().date2;
                                arr.action = "select";
                                if (arr.date_end == null || arr.date_start == null)
                                {
                                    alert("введите даты");
                                    return;
                                }
                                let layer_name = layer.getValue();
                                send_data("server.php", "POST", arr, add_markers, layer_name);
                            }},

                        {title:"clear", align:"center", formatter:button_clear, cellClick:function(e, cell){
                                let layer = cell.getData().layer;
                                clear_layer(layer);
                            }},

                    ],
                },
                {title: "socket_status", field:"status",align:"center", formatter:"color", cellClick:function(e, cell){
                       cell.setValue("green");
                    }
                },

            ],
        });



        function send_data (URL, method, data_send, callback = log, layer_name = undefined)
        {
            $.ajax({
                url: URL,
                // dataType: "json", // Для использования JSON формата получаемых данных
                method: method, // Что бы воспользоваться POST методом, меняем данную строку на POST
                data: data_send,
                success: function (data) {
                    callback(data, layer_name);
                }
            })
        };

        function log(data) {
            console.log(data);
        }


        // коллекция слоев;
        let layers = new Map();

        function add_markers(data, layer_name) {
            console.log(layer_name);
            if (layers.has(layer_name)) {
                return;
            }
            let coordinates = JSON.parse(data);
            let layer = L.geoJSON().addTo(mymap);
            layers.set(layer_name, layer);
            layer.addData(coordinates);
        }
        
        function clear_layer(layer_name) {
            if (layers.has(layer_name)) {
                let layer = layers.get(layer_name);
                console.log(layer);
                mymap.removeLayer(layer);
                layers.delete(layer_name);
            }
            return;
        }


        function IsJsonString(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }



    </script>
