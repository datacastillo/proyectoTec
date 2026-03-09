const canvas = document.getElementById('particle-canvas');
const ctx = canvas.getContext('2d');
let particles = [];

function init() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    particles = [];
    for(let i=0; i<100; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 2 + 0.5,
            speedY: Math.random() * -0.6 - 0.2,
            opacity: Math.random() * 0.7
        });
    }
}

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(212, 175, 55, ${p.opacity})`;
        ctx.fill();
        p.y += p.speedY;
        if(p.y < -10) p.y = canvas.height + 10;
    });
    requestAnimationFrame(animate);
}

const emblem = document.getElementById('emblem-tilt');
document.addEventListener('mousemove', (e) => {
    const x = (window.innerWidth / 2 - e.pageX) / 50;
    const y = (window.innerHeight / 2 - e.pageY) / 50;
    emblem.style.transform = `rotateY(${x}deg) rotateX(${-y}deg)`;
});

window.addEventListener('resize', init);
init();
animate();