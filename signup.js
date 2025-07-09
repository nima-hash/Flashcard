document.addEventListener ('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submit_Btn')
    const submitForm = async($e) => {
        $e.preventDefault()
        const form = document.getElementById('signup__form')
        const formdata = new FormData (form)
        let sanitizedData = new FormData();

        //sanitize and validate
        formdata.forEach((value, key) => {
            const cleanValue = sanitizeInput(value);
            if (key === "email" && !validateEmail(cleanValue)) {
                alert("Invalid email address");
                return;
            }
            if (key === "phone" && !/^\d+$/.test(cleanValue)) {
                alert("Phone number must contain only digits");
                return;
            }
            sanitizedData.append(key, cleanValue);
        });
        try {
            let response = await fetch("/api/register.php", {
                method: "POST",
                body: formdata, // Don't set Content-Type manually
            });
    
            let result = await response.json(); // Get raw response
            // window.location.replace("http://localhost:3000/login.php");
            console.log("Response from PHP:", result);
        } catch (error) {
            console.error("Fetch error:", error);
        }
        // try{
        //     let respons = await fetch("/api/register.php", {
        //         method: "POST",
        //         // body: formdata,
        //         body: JSON.stringify(sanitizedData),
        //         headers: { "Content-Type": "application/json" }
        //     })
        //     .then(response => response.json())
        //     .then( data => {
        //         if (data == "The user was Successfully added.") 
        //         {
        //             console.log(data)
        //         //   window.location.replace("http://localhost:3000/login.php"); 
        //         } else {
        //           console.log("unknown error: " + data);
        //         }})
        //     .catch(error => console.error("Error:", error));
        //     console.log(respons)
        // }
        // catch (error){
        //     console.error("Fetch error:", error);

        // }
       

    }
    submitBtn.addEventListener('click', submitForm)

    
    // Removes < and >
    function sanitizeInput(input) {
        return input.trim().replace(/[<>]/g, ""); 
    }
    
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
})