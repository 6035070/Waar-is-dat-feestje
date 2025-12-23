<?php
 require_once "../config/db_connect.php";
 require '../config/api/get_events.php';
 require '../config/api/add_events.php';
 require '../config/api/get_upcoming_event.php';

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Event Agenda</title>
<style>
    body { font-family: Arial, sans-serif;background: linear-gradient(to bottom, #ffffff, #f1bbffff);  margin:0; padding:10px;  height:110vh; }
    .container { display:flex; gap:60px; align-items:flex-start; 
    
    height: 70vh;
    width: 100%;}
    /* Calendar area */
    #calendarWrap { 
        display:flex;
        flex-direction:column;
        width:50%;
  
    }
    .card-panel-wrapper { 
        display:flex;
        flex-direction:column;
        width:40%;
    }

    #calendar {
        display:grid;
        grid-template-columns: repeat(7, 1fr);
        gap:12px;
        padding:10px;
        box-sizing:border-box;
        width:100%;
        height: auto;
    }
    .dayname { text-align:center; font-weight:700; padding:6px 0; }
    .box {
        background:#fff;
        min-height:120px;
        padding:8px;
        border-radius:8px;
        position:relative;
        box-shadow: 0 0 4px rgba(0,0,0,0.05);
        overflow:hidden;
    }
    .num { position:absolute; top:6px; right:8px; color:#999; font-size:12px; }
    .event {
        display:inline-block;
        background:#f0f0f0;
        padding:4px 8px;
        margin-top:20px;
        border-radius:6px;
        font-size:13px;
        cursor:pointer;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        max-width:100%;
    }

    /* Add event button */
    h1 { margin:0 0 12px 0; }
    button.addBtn { padding:10px 14px; margin-bottom:14px; }

    /* Right-side card */

    #cardPanel {
        width:320px;
        position:sticky;
        top:20px;
        background:white;
        border-radius:10px;
        padding:16px;
        box-shadow:0 6px 18px rgba(0,0,0,0.06);
        display:none; /* hidden until an event is clicked */
        z-index: 10;
        height: auto;
        width: 100%;
    }
    #cardPanel h3 { margin:0 0 8px 0; font-size:20px; }
    #cardPanel p { margin:6px 0; color:#333; font-size:14px; }
    #cardPanel img { height:60vh; width:60%; border-radius:8px; display:block; margin-top:8px; object-fit:cover; }

    /* Add Event Modal (simple) */
    #modal {
        position:fixed; top:0; left:0; right:0; bottom:0;
        background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center;
    }
    #modalBox { background:white; padding:18px; border-radius:8px; width:320px; }
    input, textarea { width:100%; padding:8px; margin:8px 0; box-sizing:border-box; }
    .small { font-size:13px; color:#666; }
</style>
</head>
<body>

<?php include '../assets/header.php'; ?>

<h1>Event Agenda</h1>
<button class="addBtn" onclick="openModal()">Add Event</button>

<div class="container">
    <div id="calendarWrap">
        <div id="calendar"></div>
    </div>

    <!-- RIGHT SIDE CARD (shows event info when clicked) -->
     <section class="card-panel-wrapper">
    <div id="cardPanel" aria-hidden="true">
        <h3 id="cardTitle"></h3>
        <p class="small" id="cardDate"></p>
        <p><strong>Time:</strong> <span id="cardTime"></span></p>
        <p><strong>Notes:</strong></p>
        <p id="cardNotes"></p>
        <img id="cardImg" src="" alt="" style="display:none;">
        <div style="text-align:right; margin-top:8px;">
            <button onclick="closeCard()">Close</button>
        </div>
    </div>
    </section>
</div>

<!-- ADD EVENT MODAL -->
<div id="modal">
    <div id="modalBox">
        <h3>Add Event</h3>
        <input type="text" id="title" placeholder="Title">
        <input type="date" id="date">
        <input type="time" id="start" placeholder="Start time">
        <input type="time" id="end" placeholder="End time">
        <textarea id="notes" placeholder="Notes"></textarea>
        <input type="text" id="img" placeholder="Image URL (direct link)">
        <div style="text-align:right;">
            <button onclick="saveEvent()">Save</button>
            <button onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
/* ---------- Calendar basic state ---------- */
let viewYear  = new Date().getFullYear();
let viewMonth = new Date().getMonth() + 1;
const calendar = document.getElementById('calendar');
const cardPanel = document.getElementById('cardPanel');

/* ---------- Render the calendar ---------- */
function renderCalendar() {
    calendar.innerHTML = '';

    // day names
    const names = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
    names.forEach(n => {
        const d = document.createElement('div');
        d.className = 'dayname';
        d.innerText = n;
        calendar.appendChild(d);
    });

    const firstDay = new Date(viewYear, viewMonth - 1, 1);
    const startOffset = (firstDay.getDay() + 6) % 7; // make Monday index 0
    const totalDays = new Date(viewYear, viewMonth, 0).getDate();

    // fetch events for this month
    fetch(`index.php?action=get_events&year=${viewYear}&month=${viewMonth}`)
        .then(r => r.json())
        .then(data => {
            // build map of date -> [events]
            const evs = {};
            if (Array.isArray(data.events)) {
                data.events.forEach(ev => {
                    evs[ev.event_date] = evs[ev.event_date] || [];
                    evs[ev.event_date].push(ev);
                });
            }

            // blank offset boxes
            for (let i = 0; i < startOffset; i++) {
                const e = document.createElement('div');
                e.className = 'box';
                calendar.appendChild(e);
            }

            for (let d = 1; d <= totalDays; d++) {
                const wrap = document.createElement('div');
                wrap.className = 'box';
                const num = document.createElement('div');
                num.className = 'num';
                num.innerText = d;
                wrap.appendChild(num);

                const iso = `${viewYear}-${String(viewMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;

                if (evs[iso]) {
                    // create a small chip for each event
                    evs[iso].forEach(ev => {
                        const evDiv = document.createElement('div');
                        evDiv.className = 'event';
                        evDiv.textContent = ev.title || '(no title)';

                        // store data safely on dataset
                        evDiv.dataset.eventId = ev.id;
                        evDiv.dataset.title = ev.title || '';
                        evDiv.dataset.date = ev.event_date || '';
                        evDiv.dataset.start = ev.start_time || '';
                        evDiv.dataset.end = ev.end_time || '';
                        evDiv.dataset.notes = ev.notes || '';
                        evDiv.dataset.img = ev.img || '';

                        wrap.appendChild(evDiv);
                    });
                }

                calendar.appendChild(wrap);
            }
        })
        .catch(err => {
            console.error('Error fetching events', err);
        });
}

renderCalendar();

function loadUpcomingEvent() {
    fetch('index.php?action=get_upcoming_event')
        .then(r => r.json())
        .then(data => {
            if (!data.event) return;

            const ev = data.event;

            document.getElementById('cardTitle').innerText = ev.title;
            document.getElementById('cardDate').innerText = ev.event_date;
            document.getElementById('cardTime').innerText =
                (ev.start_time || '') + (ev.end_time ? ' - ' + ev.end_time : '');
            document.getElementById('cardNotes').innerText = ev.notes || '';

            const imgEl = document.getElementById('cardImg');
            if (ev.img) {
                imgEl.src = ev.img;
                imgEl.style.display = 'block';
            } else {
                imgEl.style.display = 'none';
            }

            cardPanel.style.display = 'block';
            cardPanel.setAttribute('aria-hidden', 'false');
        });
}

loadUpcomingEvent();


/* ---------- Event listeners ---------- */

// open right-side card when clicking an event chip
document.addEventListener('click', function(e) {
    const ev = e.target.closest('.event');
    if (!ev) return;

    // populate card fields
    document.getElementById('cardTitle').innerText = ev.dataset.title || '(no title)';
    document.getElementById('cardDate').innerText = ev.dataset.date || '';
    const start = ev.dataset.start || '';
    const end = ev.dataset.end || '';
    document.getElementById('cardTime').innerText = start + (end ? (' - ' + end) : '');
    document.getElementById('cardNotes').innerText = ev.dataset.notes || '';

    // image handling
    const imgEl = document.getElementById('cardImg');
    if (ev.dataset.img) {
        imgEl.src = ev.dataset.img;
        imgEl.style.display = 'block';
    } else {
        imgEl.style.display = 'none';
        imgEl.src = '';
    }

    // show panel
    cardPanel.style.display = 'block';
    cardPanel.setAttribute('aria-hidden', 'false');
});

// close card
function closeCard() {
    cardPanel.style.display = 'none';
    cardPanel.setAttribute('aria-hidden', 'true');
}

/* ---------- Add event modal ---------- */
function openModal() {
    document.getElementById('modal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function saveEvent() {
    const title = document.getElementById('title').value.trim();
    const date  = document.getElementById('date').value;
    const start = document.getElementById('start').value;
    const end   = document.getElementById('end').value;
    const notes = document.getElementById('notes').value;
    const img   = document.getElementById('img').value;

    if (!title || !date || !start) {
        alert('Title, Date and Start time required');
        return;
    }

    fetch('index.php?action=add_event', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            title: title,
            date: date,
            start_time: start,
            end_time: end,
            notes: notes,
            img: img
        })
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            closeModal();
            // clear modal inputs
            document.getElementById('title').value = '';
            document.getElementById('date').value = '';
            document.getElementById('start').value = '';
            document.getElementById('end').value = '';
            document.getElementById('notes').value = '';
            document.getElementById('img').value = '';
            renderCalendar();
        } else {
            alert('Server error: ' + (resp.error || 'unknown'));
        }
    })
    .catch(err => {
        alert('Network/server error');
        console.error(err);
    });
}
</script>

</body>
</html>
