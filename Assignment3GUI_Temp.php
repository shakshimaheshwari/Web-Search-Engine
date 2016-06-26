<?php
// make sure browsers see this page as utf-8 encoded HTML

ini_set('memory_limit','-1');
header('Content-Type: text/html; charset=utf-8');


$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client/Apache/Solr/Service.php');
  include('SpellCorrector.php');
    
  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/Assignment3');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <link href="http://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script type='text/javascript' language='javascript'src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js'></script>
    <script src = "Porter-Stemmer/PorterStemmer1980.js"></script>
    <script>
    $(document).ready(function(){
        $("#q").keyup(function(){
            var inputval = $("#q").val();
            if(inputval.lastIndexOf(' ')>=0)
             {
                var last = inputval.lastIndexOf(' ');
                var other = inputval.substring(last+1,inputval.length);
                var first = inputval.substring(0,last);
                 $.ajax({
              url:'http://localhost:8983/solr/Assignment3/suggest',
              data:{'q': other,'wt':'json'},
              dataType:'jsonp',
              jsonp: 'json.wrf',
              success: function(data){
                  var i;  
                  var available_data =[];
                  var stem =[];
                  console.log(data['suggest']['suggest'][other]['numFound']);
                  
                  for(i=0; i< data['suggest']['suggest'][other]['numFound'];i++)
                      { 
                          word = first+' ';
                          var s = stemmer(data['suggest']['suggest'][other]['suggestions'][i]['term']);
                          if(stem.length == 5)
                              break;
                          if(stem.indexOf(s) == -1)
                              {
                                  stem.push(s);
                                  word += data['suggest']['suggest'][other]['suggestions'][i]['term'];
                                  available_data.push(word);
                              }
                         
                          //word += data['suggest']['suggest'][other]['suggestions'][i]['term'];
                          
                          
                      }
                  $("#q").autocomplete({
                     source: available_data
                  }); 
              }});
             }
             else
             {
              $.ajax({
              url:'http://localhost:8983/solr/Assignment3/suggest',
              data:{'wt':'json', 'q':inputval},
              dataType:'jsonp',
              jsonp: 'json.wrf',
              success: function(data){
                  var i;  
                  var available_data =[];
                  var stem=[];
                  for(i=0; i<data['suggest']['suggest'][inputval]['numFound'];i++)
                      { 
                          var s = stemmer(data['suggest']['suggest'][inputval]['suggestions'][i]['term']);
                          if(stem.length == 5)
                              break;
                          if(stem.indexOf(s) == -1)
                              {
                                stem.push(s);
                                word = data['suggest']['suggest'][inputval]['suggestions'][i]['term'];
                                available_data.push(word);
                              }
                      }
                  $("#q").autocomplete({
                     source: available_data 
                  });
              }});
             }
             
        });
         
    });
    </script>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit"/><br>
      <input type ="radio" name ="sortingalgo" id = "defaultSolr" value = "defaultSolr" <?php if(isset($_GET['sortingalgo']) && 
      $_GET['sortingalgo'] == "defaultSolr") {echo ' checked ="checked"';}?> checked>Default Solr
      <input type ="radio" name ="sortingalgo" id = "externalPageRank" value="externalPageRank"<?php if(isset($_GET['sortingalgo']) && 
      $_GET['sortingalgo'] == "externalPageRank") {echo ' checked ="checked"';}?>>External Page Rank File
    </form>
<?php
if ($query)
{
    $data = array();
  $data = explode(" ",$query);
  $querywords ="";
  $s = new SpellCorrector();
  foreach((array)$data as $queryspell)
  { 
      $words= $s->correct($queryspell);
      $querywords.= $words.' ';
  }
  $querywords = trim($querywords);
  $radiobuttonchecked = $_GET['sortingalgo'];
  if($radiobuttonchecked == "externalPageRank")
  {
      if($query != $querywords)
      {
        $link_address = 'http://localhost/Assignment3GUI_Temp.php?q='.$querywords.'&sortingalgo='.$radiobuttonchecked;
        echo "Did you mean: <a href ='".$link_address."'>".$querywords."</a>";
      }
      $additionalParameters = array(
          'sort'=>'pageRankFile desc'
      );
      
      try
      {
        $results = $solr->search($query, 0, $limit,$additionalParameters);
      }
      catch (Exception $e)
      {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
      }
  }
  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  else
  {
      if($query != $querywords)
      {
        $link_address = 'http://localhost/Assignment3GUI_Temp.php?q='.$querywords.'&sortingalgo='.$radiobuttonchecked;
        echo "Did you mean: <a href ='".$link_address."'>".$querywords."</a>";
      }
  try
  {
    $results = $solr->search($query, 0, $limit);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
  }
}
// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  $fileopen = fopen("intermediatefile.csv","r");
  foreach ($results->response->docs as $doc)
  {
      $timezone = "America/Los_Angeles";
      date_default_timezone_set("$timezone");
      $doc->title = isset($doc->title)?$doc->title:"N/A";
      $doc->author = isset($doc->author)?$doc->author:"N/A";
      $doc->date = isset($doc->date)?date('d-m-Y',strtotime($doc->date)):"N/A";
      $doc->stream_size = isset($doc->stream_size)?($doc->stream_size/1024):"N/A";
      
      //to retrive the URLs
      $doc->id = isset($doc->id)?($doc->id):"N/A";
      $frontid = "/home/shakshi/shared/DocsDownload/";
      $doc->id = str_replace($frontid,'',$doc->id);
      $doc->id =str_replace(array('!'),array('/'),$doc->id);
      $doc->id =str_replace(array('@'),array(':'),$doc->id);
      $doc->id =str_replace(array('('),array('?'),$doc->id);
      
      $linktext ="";
      $stream = $doc->stream_content_type;
      if($stream == "text/html")
      {
          $linktext = "WebPage";
          $doc->id = chop($doc->id,".html");
      }
      else if($stream == "application/pdf" || $stream == "application/msword")
          $linktext = "Document";
      
      
      
?>
      <li>
        <table>
          <tr>
              <td><a href="<?php echo $doc->id?>"><?php echo $linktext?></a></td>
          </tr>    
          <tr style="font-size:14px;">
            <td>Title:<?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?></td>
            <td>|Author:<?php echo htmlspecialchars($doc->author, ENT_NOQUOTES, 'utf-8'); ?></td>
            <td>|Date created:<?php echo htmlspecialchars($doc->date, ENT_NOQUOTES, 'utf-8');?></td>
            <td>|Size in kB:<?php echo htmlspecialchars(round($doc->stream_size), ENT_NOQUOTES, 'utf-8');?></td>
          </tr>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
