<?php
// nsid of flickr account, get here:
// http://www.xflickr.com/fusr/
define("FLICKR_NSID","YOUR_NSID_HERE");

// api key of flickr account, get/create here:
// http://www.flickr.com/services/apps/
define("FLICKR_API_KEY","YOUR_APIKEY_HERE");

// define urls for api calls
define("FLICKR_API_URL","http://www.flickr.com/services/rest/");
define("FLICKR_SET_LIST",FLICKR_API_URL
                         ."?method=flickr.photosets.getList&api_key="
                         .FLICKR_API_KEY
                         ."&user_id="
                         .FLICKR_NSID);
define("FLICKR_SET_PHOTOS",FLICKR_API_URL
                           ."?method=flickr.photosets.getPhotos&api_key="
                           .FLICKR_API_KEY
                           ."&photoset_id=");

// if $_POST superglobal has elements, we're posting
if(count($_POST))
{
  // get set id from form or set to false
  $set = isset($_POST['set'])?$_POST['set']:false;

  // retrieve photo listing from a defined set
  if($xml = simplexml_load_file(FLICKR_SET_PHOTOS."$set"))
  {
    // assign results for iteration
    if($photos = $xml->photoset->photo)
    {
      // iterate over each found photo
      foreach($photos as $photo)
      {
        // easy access to photo attributes
        $photo = $photo->attributes();

        // build photo url from attributes
        $src = "http://farm{$photo->farm}.static.flickr.com/".
               "{$photo->server}/{$photo->id}_{$photo->secret}_z.jpg";

        // build image html
        $image = "<p><img src=\"$src\" title=\"$photo->title\"/></p>\n";
        
        // print result
        print htmlentities($image,ENT_QUOTES,"utf-8");
      }
    }
    else
    {
      // if request succeeded but no results were found, show error
      $err = $xml->err->attributes();
      print "$err->msg [code: $err->code] [set: $set]";
    }
  }
  else
  {
    // if request failed entirely, show url that failed
    print "Unable to contact ".FLICKR_SET_PHOTOS."$set";
  }
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8"/>
  <title>Flickr Photo Loader</title>
  <script src="http://code.jquery.com/jquery-1.6.2.min.js">
  </script>
  <script type="text/javascript">
  // run ajax request to retrieve photos
  function loadphotos()
  {
    // assign value from set listing
    var set = $('#set').val();
    
    // if not assigned, fail gracefully
    if(!set)
    {
      alert('Please choose a set.');
      return false;
    }
    
    // flag button as processing
    $('#button').val('loading images...');

    // run request
    $.post(window.location.href,{'set':set},function(data)
    {
      // reset button text
      $('#button').val('load');
      
      // load data into textarea
      $('#images').html(data);
    });
  }
  </script>
  <style type="text/css">
  *{margin:0;padding:0}
  body
  {
    padding:10px;
    padding-bottom:0px;
    background-color:#000;
    color:#fff;
  }
  select,input
  {
    border-radius:5px;
    -moz-border-radius:5px;
    padding:3px;
  }
  textarea
  {
    margin-top:5px;
    font:11px 'courier new';
    padding:.5%;
    width:99%;
    height:200px;
    border-radius:5px;
    -moz-border-radius:5px;
    border:none;
  }
  </style>
</head>
<body>
<?php
// retrieve set listing from a defined user
if($xml = simplexml_load_file(FLICKR_SET_LIST))
{
  // assign results for iteration
  if($sets = $xml->photosets->photoset)
  {
    // print selectbox header
    print "<select name=\"set\" id=\"set\">\n";
    print "  <option value=\"\">Choose a Photo Set</option>\n";
    
    // iterate over all sets and create select options
    foreach($sets as $set)
    {
      $id = $set->attributes()->id;
      print "  <option value=\"$id\">$set->title</option>\n";
    }
    // print selectbox footer
    print "</select>";
    
    // print load button
    print "<input type=\"button\" id=\"button\" value=\"load\" onclick=\"loadphotos()\"/>\n";
    
    // print textarea to receive responses
    print "<textarea id=\"images\"></textarea>\n";
  }
  else
  {
    // if request succeeded but no results were found, show error
    $err = $xml->err->attributes();
    print "$err->msg [code: $err->code]";
  }
}
else
{
  // if request failed entirely, show url that failed
  print "Unable to contact ".FLICKR_SET_LIST;
}
?>
</body>
</html>
