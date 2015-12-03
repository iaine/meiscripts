<?php

function struct($act, $scene, $elem, $snd, $speak, $rhyme, $line) {
    $value = array(
        'act'     => $act,
        'scene'   => $scene,
        'element' => $elem,
        'snd'     => $snd,
        'speaker' => $speak,
        'rhyme'   => $rhyme, 
        'line'    => $line,
    );

    return $value;
}

// extract data takes the short code and converts into a url
function extract_data ($short) {

  $xml_str = open_file($short);  

  $reader = new XMLReader();

  if (!$reader->open($xml_str)) {
    die("Failed to open First Folio");
  }
  $mei = array();
  $num_items=0;
  $pid = 1;
  $act = 0;
  $scene = 0;
  $line = 0;
  $play = '';
  $person = array();
  $id = $name = '';
 $pers = '';
  $sex = '';
  $role = '';
  while($reader->read()) {

    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'person') {  
      $id = $reader->getAttribute('xml:id');
      $sex = $reader->getAttribute('sex');
      $role = $reader->getAttribute('role');
      $pid++;
    }

    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'persName') {  
      if ($reader->getAttribute('type') == "standard") {
          $pers = $reader->readString();
      }
    }

    if ($id) {
       $person{$id} = array('xmlid' => $id, 'name' => $pers, 'snid'=>$pid, 
                      'sex' => $sex, 'role' => $role);
    }

    // parse the play sections
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'div') {
      $divtype = $reader->getAttribute('type');
      if ($divtype == 'act') {
        $act = $reader->getAttribute('n');
        array_push($mei, struct($act, $scene, $divtype, 10 + $act, '', '', ''));
      }
      if ($divtype == 'scene') {
        $scene = $reader->getAttribute('n');
        array_push($mei, struct($act, $scene, $divtype, 50 + $scene, '', '', ''));
      }
    }

    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'sp') {
      $speaker = substr($reader->getAttribute('who'), 1);
    }
    
    if ($reader->nodeType == XMLReader::ELEMENT && ($reader->name == 'l' || $reader->name == 'p')) {
       $play = 60 + $person[$speaker]['snid'];
       $rhyme = $reader->getAttribute('rhyme');
       $ln =  $reader->getAttribute('n');
       if ($play > 60) {
         array_push($mei, struct($act, $scene, $reader->name, $play, $speaker, $rhyme, $ln));
       }
    }

    // get the types of stage direction
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'stage') {
       $type = $reader->getAttribute('type');
       if ($type == 'entrance') {
         array_push($mei, struct($act, $scene, $reader->name, 101, '','',''));
       } else if ($type == 'exit') {
         array_push($mei, struct($act, $scene, $reader->name, 102, '','',''));
       } else if ($type == 'setting') {
         array_push($mei, struct( $act, $scene, $reader->name, 103, '','',''));
       } else if ($type == 'business') {
         array_push($mei, struct($act, $scene, $reader->name, 104, '','',''));
       } else {
         array_push($mei, struct($act, $scene, $reader->name, 105, '','',''));
       }
    }
  }
  $reader->close();

  return $person;
}

function open_file($code) {
   return "http://localhost/~iainemsley/text/F-$code.xml"; 
   #return "http://firstfolio.bodleian.ox.ac.uk/download/xml/F-$code.xml";
}

/**
*  Load the data into Couch
*/
function load_couch($data) {
  $url = 'http://127.0.0.1:5984/hamlet';

  //foreach ($data as $value) {
    foreach ($data as $key => $value) {
    //post the data into the db
     $context = stream_context_create(array(
       'http' => array(
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($value)
       )
    ));
     $result =  file_get_contents($url, false, $context);
     var_dump($result);
  }

}


if (sizeof($argv) < 1) {
   die('Usage: xml_transform.php <shortcode here>');
}

$code = $argv[1];

echo "Extracting the data from $code. \n";

$drama_coords = extract_data($code);

echo "Writing the data to file";

load_couch($drama_coords);
?>
