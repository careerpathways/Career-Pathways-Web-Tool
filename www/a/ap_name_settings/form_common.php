<form enctype="multipart/form-data" action="?dryrun=true" method="POST">
    File: <input name="userfile" type="file" />
    <br>
    <br>
    <input type="hidden" name="submitted" />
    <input type="submit" value="Upload File" />
    <br />
    <br />
    <h3>Exceptions</h3>
    These words/acronyms will be ignored when proper-casing program names on import (one per line).
    <br  />
    These exceptions are shared between Roadmap and POST APN import.
    <br  />
    <br  />
    Example: Normally, <em>accounting CIS</em> would become <em>Accounting <b>Cis</b></em>. If you add "CIS" as an exception, <em>"accounting CIS"</em> would become <em>"Accounting <b>CIS</b>"</em>.
    <br />
    <textarea name="exceptions" style="width: 200px; min-height: 300px"><?= $exceptions ?></textarea>
</form>
