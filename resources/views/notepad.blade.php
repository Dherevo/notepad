<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Notepad</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

</head>
<body class="antialiased">
<div id="content"
    class="relative items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">

    <div class="sm:px-6 lg:px-8">
        <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-1">
                <header>Notepad</header>
                <section class="p-6">

                    <div id="search">
                        <input type="text" onkeyup="searchNote(this.value)" placeholder="Search note...">
                    </div>

                    <div id="new">
                        <span>Title: </span>
                        <div id="newTitle" contenteditable="true"></div>

                        <span>Content: </span>
                        <div id="newContent" contenteditable="true"></div>
                        <button onclick="createNote()">Create note</button>
                    </div>

                    <div id="notepad"></div>
                    <div id="pagination"></div>
                    <div id="results"></div>

                    <div id="modal" class="modal">
                        <div class="modal-content modal-animate-top modal-card-4">
                            <header class="modal-container modal-blue">
                                <div contenteditable="true" id="title"></div>
                            </header>
                            <div class="modal-container">
                                <div contenteditable="true" id="body"></div>
                                <input type="hidden" name="id" id="id" value="">
                            </div>
                            <footer>
                                <button onclick="updateNote()" id="save">Save note</button>
                                <button onclick="deleteNote()" id="delete">Delete note</button>
                            </footer>
                        </div>
                    </div>

                </section>
            </div>
        </div>

        <div class="flex justify-center mt-4 sm:items-center sm:justify-between">
            <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </div>
        </div>
    </div>
</div>

<script>

    /* Settings variables */
    let itemsPerPage = 6;
    let noteList = {};
    let modal = document.getElementById('modal');
    let notepad = document.getElementById("notepad");
    let results = document.getElementById("results");

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Get the notes from the API call
    function getNotes(currentPage) {
        fetch("{{route("api.index")}}")
            .then((response) => response.json())
            .then((notes) => {
                noteList = notes;
                setNotepad(notes, currentPage);
                setPagination(notes, currentPage);

            });
    }

    // Set the notes to the notepad
    function setNotepad(noteList, currentPage) {

        if (currentPage > getTotalPages(noteList)) {
            currentPage = getTotalPages(noteList);
        }

        notepad.innerHTML = "";

        let currentNote = (currentPage * itemsPerPage) - itemsPerPage;
        let firstNote = currentNote + 1;
        let lastNote = currentPage * itemsPerPage;

        if (lastNote >= noteList.length) {
            lastNote = noteList.length;
        }

        if (lastNote == 0) {
            currentNote = 0;
            firstNote = 0;
            output = "<div class='emptyNotes'>Currently there are no notes yet</div>";
        }

        while (currentNote < lastNote) {
            if (currentNote < noteList.length) {

                let noteId = noteList[currentNote].id;
                let noteTitle = noteList[currentNote].title;
                let noteBody = noteList[currentNote].body.substring(0, 20) + "...";
                let noteDate = sqlToJsDate(noteList[currentNote].created_at);
                let note = document.createElement("div");

                note.classList.add('note');
                note.setAttribute('data-id', noteId);
                note.innerHTML = '<p>' + noteTitle + '</p><p>' + noteBody + '</p>' + '<span>' + noteDate + '</span>';
                note.addEventListener("click", (event) => {
                    event.preventDefault();
                    showNote(noteId);
                });
                notepad.appendChild(note);
            }
            currentNote++;
        }
        results.innerHTML = "Notes: " + firstNote + " - " + lastNote + " of " + noteList.length;
    }

    // Set the total of pages for pagination
    function setPagination(noteList, currentPage) {

        let output = "";
        totalPages = getTotalPages(noteList);

        output += '<button id="first" data-page="1" onclick="goToPage(this);"></button>';
        output += '<button id="previous" data-page="' + (currentPage - 1) + '" onclick="goToPage(this);"></button>';

        for (let i = currentPage-1; i <= (currentPage + 1); i++) {

            if (i > 0 && i < currentPage){
                output += '<button data-page="' + [i] + '" onclick="goToPage(this);">' + [i] + '</button>';
            }

            if(i == currentPage){
                output += '<button data-page="' + [i] + '" onclick="goToPage(this);">' + [i] + '</button>';
            }

            if(i > currentPage && i <= totalPages){
                output += '<button data-page="' + [i] + '" onclick="goToPage(this);">' + [i] + '</button>';
            }
        }

        output += '<button id="next" data-page="' + (currentPage + 1) + '" onclick="goToPage(this);"></button>';
        output += '<button id="last" data-page="' + (totalPages) + '" onclick="goToPage(this);"></button>';

        document.getElementById("pagination").innerHTML = output;

        if (currentPage <= 1) {
            document.getElementById("first").setAttribute("disabled", "");
            document.getElementById("previous").setAttribute("disabled", "");
        }

        if (currentPage >= totalPages) {
            document.getElementById("next").setAttribute("disabled", "");
            document.getElementById("last").setAttribute("disabled", "");
        }

        let activePage = document.querySelector('[data-page="' + currentPage + '"]:not([id])');
        activePage.classList.add("active");

    }

    // Transform sqldate format to Js
    function sqlToJsDate(sqlDate) {
        let dateParts = sqlDate.split("-");
        let jsDate = new Date(dateParts[0] + "/" + dateParts[1] + "/" + dateParts[2].substring(0, 2)).toLocaleDateString("en-US");

        return jsDate;
    }

    // Get the total number of pages to navigate
    function getTotalPages(noteList) {

        if (noteList.length === 0) {
            return 1;
        }

        return Math.ceil(noteList.length / itemsPerPage);
    }

    // Get an specific note from the API call
    function showNote(noteId) {
        fetch("{{route('api.show', '')}}" + "/" + noteId)
            .then((response) => response.json())
            .then((result) => {

                document.getElementById("title").innerHTML = result.title;
                document.getElementById("body").innerHTML = result.body;
                document.getElementById("id").value = result.id;
                document.getElementById('modal').style.display = 'block';

            });
    }

    // Create new note
    function createNote() {

        let title = document.getElementById('newTitle');
        let body = document.getElementById('newContent');

        if (title.innerHTML.trim().length === 0 || body.innerHTML.trim().length === 0) {
            return alert("Please complete all fields before saving");
        }

        fetch("{{route('api.store', '')}}", {
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            method: 'post',
            credentials: "same-origin",
            body: JSON.stringify({
                title: title.innerHTML,
                body: body.innerHTML
            })
        })
            .then((response) => response.json())
            .then((responseData) => {

                console.log(responseData);
                let activePage = document.querySelector('#pagination button.active');
                title.innerHTML = body.innerHTML = "";

                getNotes(activePage.dataset.page);

            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function updateNote() {

        let id = document.querySelector('#id');
        let title = document.querySelector('#title');
        let body = document.querySelector('#body');

        if (title.innerHTML.trim().length === 0 || body.innerHTML.trim().length === 0) {
            return alert("Please complete all fields before saving");
        }

        fetch("{{route('api.update', '')}}" + "/" + id.value, {
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json, text-plain, */*",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": "{{csrf_token()}}"
            },
            method: 'put',
            credentials: "same-origin",
            body: JSON.stringify({
                title: title.innerHTML,
                body: body.innerHTML
            })
        })
            .then((response) => response.json())
            .then((responseData) => {
                console.log(responseData);

                let activePage = document.querySelector('#pagination button.active');
                getNotes(activePage.dataset.page);
                modal.style.display = "none";
                id.value = title.innerHTML = body.innerHTML = "";

            })
            .catch(function (error) {
                console.log(error);
            });
    }


    function deleteNote() {
        let id = document.querySelector('#id').value;

        let text = "Are you sure you want to delete this note?\nPress OK or Cancel.";

        if (confirm(text) == true) {
            fetch("{{route('api.destroy', '')}}" + "/" + id, {
                method: 'DELETE'
            })
                .then((response) => response.json()

                    .then((result) => {
                        let activePage = document.querySelector('#pagination button.active');

                        getNotes(activePage.dataset.page);
                        console.log(result);
                        modal.style.display = "none";

                    })
                )
        }
    }

    // Search note by title
    function searchNote(title) {
        let filter = title.toLowerCase();
        let filteredList = [];
        let i = 0

        noteList.forEach(
            singleNote => {
                let itemTxt = (singleNote.title).toLowerCase();
                if (itemTxt.indexOf(filter) > -1) {

                    filteredList[i] = singleNote;
                    i++;
                }
            }
        );

        setNotepad(filteredList, 1);
        setPagination(filteredList, 1);

    }

    // Move the position of the current page and get the notes
    function goToPage(page) {
        setNotepad(noteList, parseFloat(page.getAttribute("data-page")));
        setPagination(noteList, parseFloat(page.getAttribute("data-page")));
    }

    // Initialize the notepad in the first page
    getNotes(1);

</script>


</body>
</html>
