<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download of a images with site</title>
</head>
<body>
    <?php
    /*На первом этапе подключаем библиотеку PhpSpreadsheet для работы с excel файлом, в нем будет список ссылок на картинки и получаем массив со ссылками*/
        require 'vendor/autoload.php';

        use PhpOffice\PhpSpreadsheet\Spreadsheet;

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadsheet = $reader->load("example.xlsx");

        $sheetData = $spreadsheet->getActiveSheet()->toArray(); //получаем массив с кучей лишних данных
        unset($sheetData[0]); // удаление первого элемента(заголовка) "URL"

        $arr_links = array();
        foreach ($sheetData as $l) {  //выбираем из массива только ссылки, если есть пустые значения, то они тоже запишутся, их нужно будет удалить        
            $arr_links[] = $l[1];   
        }

        function array_delete(array $array, array $symbols = array('')) //пишем функцию: принимает массив и удаляет в нем интересующие символы
        {
            return array_diff($array, $symbols);
        }
        $arr_links = array_delete($arr_links, array('', 0, null)); //получаем массив только со ссылками на изображение
        //https://mysites/default/categ1/world/gdwagw46.jpg //пример результата в коцне

        /*На втором этапе обрабатываем массив ссылок из $arr_links: необходимо вырезать последнюю часть с названием файла и двумя папками, в которые он вложен. 
        Затем получаем последние две папки, их используем как путь для создания соответствующих директорий. 
        Органичения используемых регулярных варажений: название ссылок обязательно должны быть на английском языке изначально и фото тоже, скобок, тире быть не должно.
        Файлы, подподающие под ограничения просто скачиваются выше корневой папки.
        Примеры не правильных ссылок(если вы хотите убрать эти ограничения, то доработайте регулярные выражения):
        https://mysites/default/files/kcfinder/images/45hh_o(1).jpg
        https://mysites/default/files/kcfinder/images/фото 48.jpg
        https://mysites/default/files/kcfinder/images/DBSKAR0.JPG
        https://mysites/default/files/rthrsdk-ex3frt_1.png
        https://mysites/default/files/12-2.jpg
        Получаем заголовки и обрабатываем их, скачиваем файлы с сайта, в данном случае это изображения.
        */
        $dir_links = array();
        $direct = array();

        foreach($arr_links as $v){
            $short_link = preg_match_all('#(?:/\\w+){3}(?:.jpg|.jpeg|.png)$#', $v, $short); //файл и две папки после него, пример результата /categ1/world/gdwagw46.jpg
            $short = $short[0][0];   //убираем лишнее вложение у массива            
            //$dir_links[] = $short;
        
            $two_dir = preg_match_all('#/(\w+/){2}#', $short, $u_directory); //поиск двух папок, пример результата /categ1/world/
            $u_directory = $u_directory[0][0];   //убираем лишнее вложение у массива            
            //$direct[] = $u_directory;
//print_r($u_directory);
            $link_file = preg_match_all('#\w+(?:.jpg|.jpeg|.png)$#', $v, $name_file); //получаем название файла вида gdwagw46.jpg
            $name_file = $name_file[0][0];   //убираем лишнее вложение у массива
            
            $u_directory = __DIR__.$u_directory;
            if (!file_exists($u_directory))  mkdir($u_directory, 0777, true);
            //else echo 'Папки уже созданы или это ошибка';

            $Headers = @get_headers($v);  //получаем заголовки по каждой ссылке;
            $cont = (int)substr($Headers[4], 15);
            if($cont <= 2000000) { //ограничение размера изображения в битах
                if(preg_match("|200|", $Headers[0]) ) {  //проверяем существование кода 200, т.е. страница существует
                    $image = file_get_contents($v); //передаем контент ссылки $v в переменную
                    @file_put_contents($u_directory.$name_file, $image);//сохраняем полученный контент
                } else {
                    echo "Not Found";
                }
            } else echo '<br>can\'t download, it\'s very big file: '.$cont;
        }
        /*если регулярные выражения не могут обработать файл из-за не учтенных символов, то выкидывают их выше корневой папки, 
        название при это равно названию корневой папки + название файла до символа, который не был учтен в рег. выражении*/
    ?>
</body>
</html>