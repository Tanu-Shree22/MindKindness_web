<?php
require_once "config.php";
error_reporting(0);
$username = $password = $confirm_password = $full_name = $role_name = $userId = $phone = "";
$username_err = $password_err = $confirm_password_err = $name_err = $role_name_err = "";

if ($_SERVER['REQUEST_METHOD'] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Username cannot be blank";
    }
    else{
        $sql = "SELECT id FROM register WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if($stmt)
        {mysqli_stmt_bind_param($stmt, "s", $param_username);

          // Set the value of param username
          $param_username = trim($_POST['username']);

          // Try to execute this statement
          if(mysqli_stmt_execute($stmt)){
              mysqli_stmt_store_result($stmt);
              if(mysqli_stmt_num_rows($stmt) == 1)
              {
                  $username_err = "This username already exist."; 
                  echo "<script> alert('This username already exists.');
                  window.location.replace('login page.php'); </script>";
              }
              else{
                  $username = trim($_POST['username']);
              }
          }
          else{
              echo "Something went wrong";
          }
        }
      }
  
      mysqli_stmt_close($stmt);
  
  
  // Check for password
  if(empty(trim($_POST['password']))){
      $password_err = "Password cannot be blank";
  }
  elseif(strlen(trim($_POST['password'])) < 5){
      $password_err = "Password cannot be less than 5 characters";
      echo "<script> alert('Password cannot be less than 5 characters.'); 
     </script> ";
  }
  else{
      $password = trim($_POST['password']);
  }
  
  // Check for confirm password field
  if(trim($_POST['password']) !=  trim($_POST['confirm_password'])){
    $password_err = "Passwords should match";
    echo "<script> alert('passwords should match.'); </script>";
}
//name check
if(empty(trim($_POST['full_name']))){
  $name_err = "Name cannot be blank";
}
else{
  $full_name = trim($_POST['full_name']);
}
//role name check
if(empty(trim($_POST['role_name']))){
  $role_name_err = " Role Name cannot be blank";
}else{
  $role_name = trim($_POST['role_name']);
}

// If there were no errors, go ahead and insert into the database
if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($name_err)&& empty($role_name_err) )
{
    $sql = "INSERT INTO register (username, password,full_name ,role_name) VALUES (?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt)
    {
        mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_password, $param_full_name, $param_role_name);

        // Set these parameters
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_full_name = $full_name;
        $param_role_name =  $role_name;
        // $param_userId = $userId; 
       
// Try to execute the query
if (mysqli_stmt_execute($stmt))
{  if( $role_name == "customer"){
    header("location:login page.php");
}
elseif( $role_name == "employee"){
header("location: Log_employee.php");
    }

    else{
      header("location:Log_admin.php");
    }
}
else{
    echo "Something went wrong... cannot redirect!";
}
}
mysqli_stmt_close($stmt);
}
mysqli_close($conn);
}






?>
<!DOCTYPE html>

<html lang="en" dir="ltr">
  <head>
    
<title>MindKindness</title>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link href="css/register.css" rel="stylesheet" type="text/css">
   </head>
    
<body >
 
    <div id="form">
       
            <h1>Create Account</h1>
      <form name="form1" method="post">
        <div id="consent">
          <div ><br>
            <label>Email</label>
            <input type="email"  name="username" placeholder="user@abc.com" required>
           
          </div>
          <div ><br>
            <label> Full Name</label>
            <input type="text" name="full_name" placeholder="Enter your name" required>
          </div>
          <div><br>
           <label>Role Name:</label><br><br>
           <label class="container">User
  <input type="radio" name="role_name" value="customer">
  <span class="checkmark"></span>
</label>
<label class="container">Therapist
  <input type="radio" name="role_name" value="employee">
  <span class="checkmark"></span>
</label>

 
          </div>
          <!-- <div ><br>
            <label>User Id (If Employee)</label>
            <input type="text" name="userId" placeholder="Enter your Id" >
          </div>
          <div><br> -->
         
          <div ><br>
            <label> Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
          </div>
          <div ><br>
            <label> Confirm Password</label>
            <input type="text" name="confirm_password" placeholder="confirm password" required>
          </div>

        </div>
        
       
          <div>
            <br>
            <!-- <a href="main page.html"> -->
            <button  type="submit" onclick="phonenumber(document.form1.phone)">Register</button>
        </div>
          <div>
            <h3>Already have an account? <a href="login_page.php">Login now</a></h3>
          </div>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
