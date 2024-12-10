// =====================================
// Dashboard Mini dengan Grafik, Keuntungan, dan Total Balance
// =====================================

// Inisialisasi variabel keuntungan
let totalProfit = 0; // Total keuntungan dalam coin
let totalBet = 0; // Total coin yang di-bet

// Elemen Dashboard
const container = document.createElement("div");
container.id = "dashboardMini";
container.style.cssText = `
    font-family: Arial, sans-serif;
    background-color: rgba(47, 47, 47, 0.8);
    padding: 20px;
    border-radius: 12px;
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 1000;
    box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    cursor: move;
`;

// Struktur Dashboard
container.innerHTML = `
    <div id="totalBalanceDisplay" style="
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        color: #ffa500;
        margin-bottom: 10px;
    ">Total Balance: 0.00000000</div>
    <div id="profitDisplay" style="
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        color: #008000;
        margin-bottom: 20px;
    ">0.00000000 [0%]</div>
    <div style="border: 1px solid #fff; border-radius: 5px; padding: 5px; background-color: #2f2f2f;">
        <canvas id="movingChart" style="width: 100%; height: 120px;"></canvas>
    </div>
`;
document.body.appendChild(container);

// Fungsi untuk membuat elemen bisa digeser
function makeDraggable(element) {
    let offsetX = 0, offsetY = 0, isDragging = false;

    element.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - element.offsetLeft;
        offsetY = e.clientY - element.offsetTop;
        element.style.position = "absolute";
        element.style.zIndex = 1000;
    });

    document.addEventListener("mousemove", (e) => {
        if (isDragging) {
            element.style.left = `${e.clientX - offsetX}px`;
            element.style.top = `${e.clientY - offsetY}px`;
        }
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
    });
}

makeDraggable(container);

// Elemen Grafik
const canvas = document.getElementById("movingChart");
const ctx = canvas.getContext("2d");
canvas.width = canvas.offsetWidth;
canvas.height = canvas.offsetHeight;

let dataPoints = [50]; // Data awal
let maxDataPoints = 50; // Maksimum jumlah titik

// Fungsi untuk menggambar grafik area-chart
function drawChart() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.beginPath();
    ctx.lineWidth = 2;
    ctx.strokeStyle = "#28a745"; // Warna garis hijau
    ctx.fillStyle = "rgba(40, 167, 69, 0.2)"; // Warna area hijau transparan

    for (let i = 0; i < dataPoints.length; i++) {
        let x = (i / (dataPoints.length - 1)) * canvas.width; // Posisi X proporsional
        let y = canvas.height - (dataPoints[i] / 100) * canvas.height; // Skala 0-100
        if (i === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    }

    ctx.lineTo(canvas.width, canvas.height);
    ctx.lineTo(0, canvas.height);
    ctx.closePath();
    ctx.fill();
    ctx.stroke();
}

// Fungsi untuk memperbarui data grafik
function updateChart(betResult, amount) {
    let lastPoint = dataPoints[dataPoints.length - 1] || 50;

    // Tentukan perubahan nilai berdasarkan hasil taruhan
    let change = 0;
    if (betResult === "bad") {
        change = -(Math.random() * amount + 1); // Fluktuasi turun
    } else if (betResult === "good") {
        change = Math.random() * amount + 1; // Fluktuasi naik
    }

    let newPoint = Math.max(0, Math.min(100, lastPoint + change)); // Jaga nilai antara 0-100
    dataPoints.push(newPoint);

    if (dataPoints.length > maxDataPoints) {
        dataPoints.shift(); // Hapus titik lama
    }

    drawChart();
}

// Fungsi untuk memperbarui keuntungan di dashboard
function updateProfitDisplay() {
    const balanceElement = document.getElementById("coin-value");
    const balance = balanceElement ? parseFloat(balanceElement.textContent.trim()) : 0;
    const profitPercentage = balance > 0 ? ((totalProfit / balance) * 100).toFixed(2) : 0;
    document.getElementById("profitDisplay").textContent = `${totalProfit.toFixed(8)} [${profitPercentage}%]`;
}

// Fungsi untuk membaca dan memperbarui total balance secara real-time
function updateTotalBalance() {
    const balanceElement = document.getElementById("coin-value");
    if (balanceElement) {
        const balance = balanceElement.textContent.trim(); // Baca nilai balance dari elemen
        document.getElementById("totalBalanceDisplay").textContent = `Total Balance: ${balance}`;
    } else {
        console.warn("Elemen balance tidak ditemukan.");
    }
}

// Observer untuk memantau perubahan pada elemen balance
function observeBalanceChanges() {
    const balanceElement = document.getElementById("coin-value");
    if (!balanceElement) {
        console.warn("Elemen balance tidak ditemukan untuk observer.");
        return;
    }

    const observer = new MutationObserver(() => {
        // Perbarui dashboard saat elemen balance berubah
        updateTotalBalance();
        updateProfitDisplay();
    });

    // Konfigurasi observer untuk memantau perubahan teks
    observer.observe(balanceElement, {
        childList: true, // Pantau perubahan anak elemen
        characterData: true, // Pantau perubahan karakter
        subtree: true, // Pantau semua perubahan di dalam elemen
    });

    // Perbarui balance pertama kali
    updateTotalBalance();
    updateProfitDisplay();
}

// Panggil fungsi observer
observeBalanceChanges();

// Fungsi untuk memeriksa hasil taruhan
function checkResult(resultText) {
    const valueMatch = resultText.match(/-?\d+\.\d+/); // Menangkap angka hasil taruhan
    const value = valueMatch ? parseFloat(valueMatch[0]) : 0;

    if (resultText.includes("won") || resultText.includes("+")) {
        console.log(`%cwin ${value} DOGE (hijau)`, "color: green; font-weight: bold;");
        const profit = value; // Profit = hasil menang
        totalProfit += profit; // Tambahkan ke total keuntungan
        totalBet += value / 2; // Misal bet marti-marti, hitung sebagai separuhnya
        updateProfitDisplay(); // Perbarui dashboard
        updateChart("good", profit); // Grafik naik
        return "good";
    } else if (resultText.includes("lose") || resultText.includes("-")) {
        console.log(`%close ${value} DOGE (merah)`, "color: red; font-weight: bold;");
        totalBet += Math.abs(value); // Tambahkan ke total bet
        updateProfitDisplay(); // Perbarui dashboard
        updateChart("bad", Math.abs(value)); // Grafik turun
        return "bad";
    }
    return null;
}

// Fungsi utama bot
async function startBot() {
    const actions = ["BetLowButton", "BetHighButton"];
    let currentActionIndex = 0; // Mulai dengan tombol Low

    // Inisialisasi MutationObserver untuk hasil taruhan
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === "childList" || mutation.type === "characterData") {
                const resultElement = document.getElementById("LastBetInfoContainer");
                if (resultElement) {
                    const resultText = resultElement.textContent.trim().toLowerCase();
                    const result = checkResult(resultText);

                    if (result === "good") {
                        clickButton("BetResetButton"); // Klik tombol Reset
                        currentActionIndex = (currentActionIndex + 1) % actions.length; // Pindah ke aksi berikutnya
                    } else if (result === "bad") {
                        clickButton("MultiplyBetButtonCr"); // Klik tombol Double
                    }
                }
            }
        });
    });

    const targetNode = document.getElementById("LastBetInfoContainer");
    if (targetNode) {
        observer.observe(targetNode, {
            childList: true,
            characterData: true,
            subtree: true,
        });
    } else {
        console.warn("Elemen LastBetInfoContainer tidak ditemukan untuk observer.");
    }

    // Jalankan klik tombol dengan waktu lebih cepat
    while (true) {
        const action = actions[currentActionIndex];
        clickButton(action); // Klik tombol Low atau High

        await new Promise((resolve) => setTimeout(resolve, 1)); // Delay hanya 1ms
    }
}

// Fungsi untuk memberikan efek klik
function simulateClickEffect(button) {
    if (!button) return;

    button.style.transition = "background-color 0.2s, transform 0.1s";
    button.style.backgroundColor = "#ffcccb"; // Efek visual klik
    button.style.transform = "scale(0.95)";

    setTimeout(() => {
        button.style.backgroundColor = "";
        button.style.transform = "scale(1)";
    }, 1); // Efek lebih cepat
}

// Fungsi untuk klik tombol
function clickButton(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) {
        simulateClickEffect(button);
        button.click();
    } else {
        console.warn(`Tombol "${buttonId}" tidak ditemukan.`);
    }
}

// Jalankan bot
startBot();
