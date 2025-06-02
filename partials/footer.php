</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Live Clock
        function updateClock() {
            const liveClockElement = document.getElementById('live-clock');
            if (liveClockElement) {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('en-US', {hour12: false});
                liveClockElement.textContent = timeStr;
            }
        }
        setInterval(updateClock, 1000);
        
        // Sidebar Toggle for Mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>
</html>