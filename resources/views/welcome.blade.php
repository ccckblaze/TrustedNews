<!DOCTYPE html>
<html>
    <head>
        <title>TrustedNews</title>

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 64px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">可信闻, By {{$author}}</div>
                <form id="frm1" action="/search">
                    标题: <input type="title" name="title">
                    <br/>
                    内容: <input type="text" name="text">
                    <br/>
                    <input type="button" onclick="myFunction()" value="搜索">
                </form>
                <script>
                    function myFunction() {
                        document.getElementById("frm1").submit();
                    }
                </script>
            </div>
        </div>
    </body>
</html>
