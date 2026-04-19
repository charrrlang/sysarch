<div id="notif-toast"></div>


    <script>
        function checkNotifs() {
            fetch('check_notifications.php')
            .then(res => res.json())
            .then(data => {
                if (data.new_announcement || data.session_update) {
                    var toast = document.getElementById("notif-toast");
                    toast.innerHTML = "<strong>🔔 Notification</strong><br>" + data.message;
                    toast.className = "show";
                    setTimeout(() => { 
                        toast.className = toast.className.replace("show", ""); 
                    }, 5000);
                }
            })
            .catch(err => console.error("Error:", err));
        }
        checkNotifs();
        setInterval(checkNotifs, 10000);
    </script>
</body>
</html>