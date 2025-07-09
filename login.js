document.addEventListener ('DOMContentLoaded', function (){
    const submitButton = document.getElementById('submitLogin')
    

    const login = async(event) => {
        event.preventDefault()
        const form = submitButton.closest('form')
        const formData = new FormData (form);
        formData.append('action', 'login');

        const sanitizedData = new FormData();

        formData.forEach((value, key) => {
            const cleanValue = sanitizeInput(value);
            
            sanitizedData.append(key, cleanValue);
        });
        // try {
        //     let response = await fetch("/api/register.php", {
        //         method: "POST",
        //         body: formdata, // Don't set Content-Type manually
        //     });
    
        //     let result = await response.json(); // Get raw response
        //     // window.location.replace("http://localhost:3000/login.php");
        //     console.log("Response from PHP:", result);
        // } catch (error) {
        //     console.error("Fetch error:", error);
        // }
        console.log(sanitizedData)
        const loginData = {
            method: 'POST',
            body: formData,
            // body:cards,
            // headers: { 
            //   'Content-Type' : 'application/json'
            }   

        const response = await runFetch('http://localhost:3000/api/register.php', loginData)

    }
    submitButton.addEventListener('click', login)

    const runFetch = async (url, requestOptions)=>{
        const result = await fetch(url, requestOptions)
        // .then(res=>res.json())
        .then(res => {
          if (res.ok) {
            return res.json();
          }
    
          throw new Error (res.statusText)
          
        })
        .then(data=>{return data})
        .catch(error => {
            console.log(JSON.stringify(error))
            // tinderModal(error)
        });
        return result;
      }

      // Removes < and >
    function sanitizeInput(input) {
        return input.trim().replace(/[<>]/g, ""); 
    }
})