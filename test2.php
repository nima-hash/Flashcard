<html>
<head>
</head>
<body>

<?php
$array = array('n1' => 'b1', 'n2' => 'b2', 'n3' => 'b3');
$b='b2';
$arrFinal = array();
array_walk($array, function($val, $key, $b) use (&$arrFinal){
    echo $key."\n";
    if ($val == $b ) {
        //Don't do anything
    } else {
       $arrFinal[$key] = $val;
    }
},$b);

print_r($arrFinal);
?>
    <form id="form" method="get">
       <input type="text", id="name" placeholder="Name"/></br>
       <input type="text", id="body" placeholder="Body"/></br>
       <input type="submit" value="Add"/>
    </form> 
    <div>
    <h3>The Following data is successfuly posted</h3>
    <h4 id="title"></h4>
    <h5 id="bd"></h5>
    </div>
</body>
<script>
var form=document.getElementById('form')

form.addEventListener('submit', function(e){
 e.preventDefault()

 var name=document.getElementById('name').value
 var body=document.getElementById('body').value

 fetch('http://localhost:3000/api', {
  method: 'GET',
  // body: JSON.stringify({
  //   title:name,
  //   body:body,

  // }),
  // headers: {
  //   'Content-type': 'application/json; charset=UTF-8',
  // }
  })
  .then(function(response){ 
  return response.json()})
  .then(function(data)
  {console.log(data)
  title=document.getElementById("title")
  body=document.getElementById("bd")
  title.innerHTML = data.title
  body.innerHTML = data.body  
}).catch(error => console.error('Error:', error)); 
});
</script>
</html>