<?php
// footer.php
// This file closes the main tags and can be used for scripts or footer text.
?>
        </main>
        <footer>
            <p>Front-end for MARU database. Group DVWA</p>
        </footer>
    </div>
</body>
</html>
<?php
// Close the database connection at the very end of the page load.
if (isset($conn)) {
    mysqli_close($conn);
}
?>
