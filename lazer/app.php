<?php
    session_start();
    if ($_SESSION['id']) {
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="js/leaflet.css"/>
    <link rel="stylesheet" href="vendor/Tabulator/dist/css/tabulator.css"/>
    <link rel="stylesheet" href="js/dist/jquery.datetimepicker.css">
    <script src="js/dist/jquery-1.11.1.min.js"></script>
    <script src="js/dist/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/tabulator.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/modules/format.min.js"></script>
    <script type="text/javascript" src="js/leaflet.js"></script>
    <script type="text/javascript" src="vendor/Tabulator/dist/js/javascript2.js"></script>
    <title>laser</title>
    <style>
        body, html {
            padding: 0;
            margin: 0;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            font-family: Montserrat, sans-serif;
        }
        #main_form {
            border-bottom: 1px solid #86d9c0;
            box-shadow: 0 0 5px black;
        }
        header {
            /*position:absolute;*/
            height: 25px;
            background: #b5e3c0;
            border-bottom: 1px solid #98d98f;
            box-shadow: 0 0 5px black;
        }
        #info {
            position: absolute;
            margin-right: 10px;
            padding: 5px 10px;
            width: 40px;
            right: 0;
            /*border: 1px solid black;*/
        }
        a {
            color: #7c9ab7;
            font-weight: bold;
            text-decoration: none;
        }

    </style>
</head>
<body>
    <header>
        <div id="info"><a href="logout.php?logout=1">Выйти</a></div>
    </header>
    <div id="main_form" style="margin: 5px;">
        <div id = "example-table"></div> <br/>

        <div id="leaflet" style="position: relative">
            <div id="mapid" style="z-index: 0; height: 620px;"></div>
        </div>
    </div>
</body>
</html>


<script type="text/javascript">
    //получаем таблицу

    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
    let username = getCookie('web_socket_id');


    let table;
    (async function onloadTable() {
        let tabledata = await fetch('get_data.php?id='+username).then((response) => {
            return response.json()
        });
        table = new Tabulator("#example-table", {
            autoResize: false,
            selectable: false,
            data: tabledata,
            layout: "fitDataFill",
            columns: [
                {title: "id", field: "id", align: "center", width: 48, responsive: 0},
                {title: "#otm", field: "otm", width: 70},
                {title: "Object", field: "object", width: 100, align: "left"}, //formatter:"progress"
                {title: "imei", field: "imei", width: 130},
                {title: "Start", field: "start_date", align: "center"},
                {title: "End", field: "end_date", sorter: "date", align: "center"},
                {title: "wm", field: "work_mode", width: 56, editor: false, align: "center"},
                {title: "voice", align: "center",
                    columns: [
                        {
                            title: "min",
                            field: "duration",
                            align: "center",
                            editor: "input",
                            validator: "integer",
                            cellEdited: function (cell) {
                                let arr = {};
                                arr.imei = cell.getData().imei;
                                arr.duration = cell.getValue();
                                arr.table_name = 'otm_voice';
                                arr.action = 'update';
                                send_data('server.php', 'POST', arr);
                            },
                        },
                        {
                            title: 'datetime',
                            field: "date_voice",
                            align: "center",
                            width: 210,
                            formatterParams: "loc",
                            formatter: dateedit
                        }
                    ],
                },
                {
                    title: "loc",
                    field: "location_interval",
                    align: "center",
                    editor: true,
                    validator: "integer",
                    cellEdited: function (cell) {
                        let arr = {};
                        arr.imei = cell.getData().imei;
                        arr.location_interval = cell.getValue();
                        arr.table_name = 'otm_loc';
                        arr.action = 'update';
                        send_data('server.php', 'POST', arr);
                    },
                    cellMouseEnter: function (e, cell) {
                        // менять курсор мыши
                    },
                },
                {
                    title: "send",
                    formatter: button_send,
                    field: "example",
                    align: "center",
                    cellClick: function (e, cell) {
                        let row = cell.getRow();
                        let wm = cell.getData().work_mode;
                        let locInt = cell.getData().location_interval;
                        let duration = cell.getData().duration;
                        // let battery = row.getCell('battery');
                        // let space = row.getCell('space');
                        // let datetime = row.getCell('datetime');
                        let now = new Date();
                        let date_start_record_value = row.getCell('date_voice')._cell.element.childNodes[0].childNodes[0].value;
                        let date_start_record = new Date(date_start_record_value);
                        // console.log(date_start_record_value + ' - ' + date_voice);
                        let arr = {};

                        if (duration != 0) {
                            if (Number.isInteger(date_start_record.valueOf())) {
                                if (date_start_record.valueOf() <= now.valueOf()) {
                                    alert("Дата начала записи должна быть больше текущего времени!");
                                    // console.log();
                                    return;
                                }
                                let diff = date_start_record.valueOf() - now.valueOf();
                                let diffInHours = diff / 1000 / 60;
                                arr.duration_start_time = Math.floor(diffInHours);
                            }
                            arr.duration = duration;
                        }
                        arr.imei = cell.getData().imei;
                        arr.webSender = username;
                        arr.action = 'send_info';
                        arr.workMode = wm;
                        arr.locInterval = locInt;
                        console.log(arr);
                        setTimeout(function () {
                            socket.send(JSON.stringify(arr));
                        }, 1000);
                    }
                },
                {
                    title: "active",
                    field: "active",
                    width: 55,
                    align: "center",
                    formatter: "tickCross",
                    cellClick: function (e, cell) {
                        let arr = {};
                        arr.imei = cell.getData().imei;
                        arr.webSender = username;
                        arr.table_name = 'activeLocation';
                        console.log(cell.getValue());
                        if (cell.getValue() == null) {
                            arr.action = 'insert';
                            cell.setValue(1);
                        } else {
                            cell.setValue(null);
                            arr.action = 'delete';
                        }
                        send_data('server.php', 'POST', arr);
                    }
                },
                {
                    title: "Местоположение", width: 550, align: "center",
                    columns: [
                        {
                            title: "c",
                            field: "date1",
                            align: "center",
                            width: 210,
                            formatterParams: "start",
                            formatter: dateedit
                        },
                        {
                            title: "по",
                            field: "date2",
                            align: "center",
                            width: 210,
                            formatterParams: "end",
                            formatter: dateedit
                        },
                        {title: "layer", field: "layer", align: "center", visible: false},
                        {
                            title: "get",
                            width: 55,
                            formatter: button_send,
                            align: "center",
                            cellClick: function (e, cell) {
                                let row = cell.getRow();
                                // console.log();
                                let rowIndex = row.getIndex();
                                let layer = row.getCell('layer');
                                layer.setValue('Layer_' + rowIndex);
                                let arr = {};
                                arr.imei = cell.getData().imei;
                                arr.webSender = username;
                                arr.date_start = row.getCell('date1')._cell.element.childNodes[0].childNodes[0].value;
                                arr.date_end = row.getCell('date2')._cell.element.childNodes[0].childNodes[0].value;
                                arr.action = 'select';
                                // arr.action = 'check';
                                // socket.send(JSON.stringify(arr));
                                arr.table_name = 'location';
                                if (arr.date_end == null || arr.date_start == null)
                                {
                                    alert("введите даты");
                                    return;
                                }
                                let layer_name = layer.getValue();
                                console.log(arr);
                                send_data("server.php", "POST", arr, add_markers, layer_name);

                            }
                        },

                        {
                            title: "clear",
                            width: 55,
                            align: "center",
                            formatter: button_clear,
                            cellClick: function (e, cell) {
                                let layer = cell.getData().layer;
                                clear_layer(layer);
                            }
                        },
                    ],
                },
                {
                    title: "device_status",
                    field: "status",
                    align: "center",
                    // formatter: "color",
                    cellClick: function (e, cell) {
                        // cell.setValue("green");
                    },
                    columns: [
                        {title: "bat", field: "battery", align: "center"},
                        {title: "space", field: "space", align: "center"},
                        {title: "time", field: "datetime", align: "center"},
                    ],
                },
                {
                    title: "getAll",
                    field: "getAll",
                    align: "center",
                    formatter: button_send,
                    cellClick: function (e, cell) {
                        let arr = {};
                        arr.imei = cell.getData().imei;
                        arr.workMode = cell.getData().work_mode;
                        arr.webSender = username;
                        arr.action = 'getFiles';
                        // arr.mode = [{'getFiles': '1'}];
                        // console.log(JSON.stringify(arr))
                        socket.send(JSON.stringify(arr));
                    },
                },
            ],
        });
    })();

    let layers = new Map();


    let mymap = L.map('mapid').setView([53.8981064, 27.5449547], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy',
        maxZoom: 18
    }).addTo(mymap);


    let colors = ['black', 'blue', 'green', 'grey', 'orange', 'red', 'violet', 'yellow'];
    let Icon;

    function getIcon(color) {
        return L.icon({
            iconUrl: 'js/images/marker-icon-2x-' + color + '.png',
            iconSize: [38, 55],
            iconAnchor: [22, 54],
            popupAnchor: [-3, -46],
            shadowUrl: 'js/images/marker-shadow.png',
            shadowSize: [68, 55],
            shadowAnchor: [22, 54]
        });

    }


    let socket;

    (async function start_socket() {

        let host = "wss://lazer.test:8003";
        socket = new WebSocket(host);
        socket.onopen = function () {
            console.log(username);
            socket.send(username);
            console.log("Соединение установлено.");
        };

        socket.onclose = function (event) {
            if (event.wasClean) {
                console.log('Соединение закрыто чисто');
            } else {
                console.log('Обрыв соединения'); // например, "убит" процесс сервера
            }
            ;
            setTimeout(function () {
                start_socket()
            }, 3000);
            console.log('Код: ' + event.code + ' причина: ' + event.reason);
        };

        socket.onmessage = function (event) {

            if (IsJsonString(event.data)) {
                let data = JSON.parse(event.data);
                console.log(event.data);
                switch (data.type) {
                    case 'Feature':
                        let layer = layers.get(data.properties['info']);
                        let deviation = parseInt(data.properties['deviation'], 10);
                        // console.log(typeof);

                        let info = "<b>" + data.properties['info'] +
                            "</b></br>" + data.properties['datetime'] +
                            "</br>deviation: " + data.properties['deviation'] + "м" +
                            "</br>speed: " + data.properties['speed'] + "м/с";

                        if (typeof layer === 'object') {
                            // console.log(layer.circle.getLayers()[0]);
                            let point = layer.point.getLayers()[0];
                            let circle = layer.circle.getLayers()[0];
                            let latitude = data.geometry['coordinates'][0];
                            let longitude = data.geometry['coordinates'][1];
                            point.setLatLng([longitude, latitude]);
                            point.bindPopup(info).openPopup();
                            circle._mRadius = deviation;
                            circle.setLatLng([longitude, latitude]);
                        } else {
                            if (colors.length !== 0) {
                                Icon = getIcon(colors.shift());
                            } else {
                                alert("Не более 8 объектов на карте!")
                                return;
                            }
                            let myLayer = [];
                            myLayer['point'] = L.geoJSON(data, {
                                pointToLayer: function (feature, latlng) {
                                    return L.marker(latlng, {icon: Icon});
                                },
                            },).addTo(mymap);
                            myLayer['circle'] = L.geoJSON((data), {
                                pointToLayer: function (feature, latlng) {
                                    return L.circle(latlng, deviation);
                                },
                            },).addTo(mymap);
                            myLayer['point'].bindPopup(info).openPopup();
                            layers.set(data.properties['info'], myLayer);
                        }
                        break;
                    case 'LineString':
                        console.log('LineString');
                        break;
                    case 'device_status':
                        let imei;
                        let rows = table.getRows();
                        for (let i = 0; i < rows.length; i++) {
                            imei = rows[i].getCell('imei');
                            if (imei.getValue() == data.imei) {
                                let row = rows[i];
                                row.getCell('battery').setValue(data.battery);
                                row.getCell('space').setValue(data.space);
                                row.getCell('datetime').setValue(data.datetime);
                            }
                        }
                        break;
                    case 'audio':
                        alert("Imei - " + data.imei + ". " + data.record);
                }
            } else {
                console.log(event.data);
                return;
            }
        };
    })(username);

    function send_data(URL, method = undefined, data_send, callback = log, layer_name = undefined) {
        // console.log(data_send);
        $.ajax({
            url: URL,
            // dataType: "json", // Для использования JSON формата получаемых данных
            type: method, // Что бы воспользоваться POST методом, меняем данную строку на POST
            data: data_send,
            success: function (data) {
                callback(data, layer_name);
            }
        })
    };

    function log(data) {
        console.log(data);
    }

    function add_markers(data, layer_name) {
        // console.log(data);
        if (layers.has(layer_name)) {
            alert("Очиститет карту");
            return;
        } else if (isEmpty(data)) {
            alert("Координат нет в БД или измените запрашиваемый интервал времени")
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
        // return;
    }


    function IsJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    function isEmpty(str) {
        return (!str || 0 === str.length);
    }

    </script>

<?php
    } else {
        header('Location: login.php');
    }