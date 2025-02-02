<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="PHPrefresh.php" method="post" style="display:flex; color:brown; flex-direction:column" >
        <label for="vorname">Vorname</label>
        <input type="text" name="name[]" id="vorname" >
        <label for="nachname">Nachname</label>
        <input type="text" name="name[]" id="nachname" >
        <label for="email">Email</label>
        <input type="email" name="name[]" id="email" >
        <input type="submit" value="Submit" name="submit">
    </form>
<?php
$test=null;
if (isset($_POST['submit']))
    {
        print_r($_POST);

        /*foreach ($_POST as $key => $value) {
           echo "{$key} is {$value} <br>";
        }*/
}else{
    echo"nothing happened!";
    print_r($_POST);
};
?>
<script>
    const people = [
  { name: "Alice", age: 21 },
  { name: "Bob", age: 25 },
  { name: "Charlie", age: 21 },
];

const groupedByAge = people.reduce((acc, person) => {
  const { age, name } = person;
  acc[age] = acc[age] && [];
  acc[age].push(name);
  return acc;
}, {});

console.log(groupedByAge);
// Output: { 21: ["Alice", "Charlie"], 25: ["Bob"] }

</script>
</body>
</html>