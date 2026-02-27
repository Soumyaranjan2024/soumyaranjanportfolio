// Fun Interactions & Easter Eggs Logic

// Pop Sound Base64 (Short clean pop)
const POP_SOUND = new Audio("data:audio/wav;base64,UklGRigAAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQQAAAAAAP8A/wD/AP8=");

function playPop() {
    POP_SOUND.currentTime = 0;
    POP_SOUND.play().catch(e => console.log("Sound play blocked"));
}

// Random Jokes
const jokes = [
    "Why do programmers prefer dark mode? Because light attracts bugs.",
    "A SQL query walks into a bar, walks up to two tables, and asks, 'Can I join you?'",
    "How many programmers does it take to change a light bulb? None, that's a hardware problem.",
    "Real programmers count from 0.",
    "I'd tell you a joke about UDP, but you probably wouldn't get it.",
    "!false - It's funny because it's true."
];

function showRandomJoke() {
    const joke = jokes[Math.floor(Math.random() * jokes.length)];
    const bubble = document.createElement('div');
    bubble.className = 'joke-bubble';
    bubble.innerHTML = `<strong>Joke of the day:</strong><br>${joke}`;
    document.body.appendChild(bubble);
    
    setTimeout(() => {
        bubble.style.opacity = '0';
        setTimeout(() => bubble.remove(), 500);
    }, 6000);
}

// Floating Emoji Effect
function spawnEmoji(emoji, x, y) {
    const el = document.createElement('div');
    el.className = 'floating-emoji';
    el.innerHTML = emoji;
    el.style.left = x + 'px';
    el.style.top = y + 'px';
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// Forbidden Button Logic
function breakPortfolio() {
    playPop();
    document.body.classList.add('broken-state');
    const fixBtn = document.getElementById('fix-it-btn');
    if (fixBtn) fixBtn.style.display = 'block';
    
    // Add a message
    const msg = document.createElement('div');
    msg.style.cssText = "position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-180deg); font-size:3rem; font-weight:900; color:red; z-index:4000; text-align:center; background:white; padding:20px; border-radius:10px;";
    msg.id = "broken-msg";
    msg.innerHTML = "Now you broke my portfolio. 😡";
    document.body.appendChild(msg);
}

function fixPortfolio() {
    playPop();
    document.body.classList.remove('broken-state');
    const fixBtn = document.getElementById('fix-it-btn');
    if (fixBtn) fixBtn.style.display = 'none';
    const msg = document.getElementById('broken-msg');
    if (msg) msg.remove();
}

// Hire Me Logic
function handleHireClick(answer) {
    playPop();
    const modal = document.getElementById('hire-modal');
    if (!modal) return;

    if (answer === 'yes') {
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 }
        });
        modal.innerHTML = `
            <h2>WOHOOO! 🎉</h2>
            <p>Best decision ever! Let's build something amazing.</p>
            <button onclick="closeHireModal()" class="btn btn-primary">Let's Go!</button>
        `;
    } else {
        document.body.classList.add('shake');
        setTimeout(() => document.body.classList.remove('shake'), 500);
        
        spawnEmoji('💔', window.innerWidth / 2, window.innerHeight / 2);
        
        modal.innerHTML = `
            <h2>My WiFi feelings are hurt 💔</h2>
            <p>Are you sure? I'm very efficient!</p>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button onclick="handleHireClick('yes')" class="btn btn-primary">Fine, I'll hire you</button>
                <button onclick="closeHireModal()" class="btn btn-secondary">Still No</button>
            </div>
        `;
    }
}

function openHireModal() {
    const modal = document.getElementById('hire-modal');
    if (modal) modal.classList.add('active');
}

function closeHireModal() {
    const modal = document.getElementById('hire-modal');
    if (modal) modal.classList.remove('active');
}

// Initialize
window.addEventListener('load', () => {
    setTimeout(showRandomJoke, 2000);
    
    // Global pop sound on any primary button click
    document.querySelectorAll('.btn-primary, .btn-secondary, .btn-forbidden').forEach(btn => {
        btn.addEventListener('click', playPop);
    });
});
