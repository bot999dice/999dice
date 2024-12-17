// =====================================
// Script Inspect>console - 999dice.net (17 Dec 2024)
// =====================================

// Initialize the profit variable
let totalProfit = 0; // Total profit in coins
let totalBet = 0; // Total coins bet

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

// Function to make elements slidable
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
            element.style.left = ${e.clientX - offsetX}px;
            element.style.top = ${e.clientY - offsetY}px;
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

let dataPoints = [50]; // Initial data
let maxDataPoints = 50; // Maximum number of points

// Function to draw area-chart graph
function drawChart() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.beginPath();
    ctx.lineWidth = 2;
    ctx.strokeStyle = "#28a745";// Green line color
    ctx.fillStyle = "rgba(40, 167, 69, 0.2)"; // Transparent green area color

    for (let i = 0; i < dataPoints.length; i++) {
        let x = (i / (dataPoints.length - 1)) * canvas.width; // Proportional X position
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

// Function to update chart data
function updateChart(betResult, amount) {
    let lastPoint = dataPoints[dataPoints.length - 1] || 50;

    // Determine the change in value based on the betting result
    let change = 0;
    if (betResult === "bad") {
        change = -(Math.random() * amount + 1); // Downward fluctuation
    } else if (betResult === "good") {
        change = Math.random() * amount + 1; // Fluctuation up
    }

    let newPoint = Math.max(0, Math.min(100, lastPoint + change)); // Keep value between 0-100
    dataPoints.push(newPoint);

    if (dataPoints.length > maxDataPoints) {
        dataPoints.shift(); // Delete old point
    }

    drawChart();
}

// Function to update profit on dashboard
function updateProfitDisplay() {
    const balanceElement = document.getElementById("coin-value");
    const balance = balanceElement ? parseFloat(balanceElement.textContent.trim()) : 0;
    console.log(totalProfit);
    console.log(balance);
    const profitPercentage = balance > 0 ? ((totalProfit / balance) * 100).toFixed(2) : 0;
    document.getElementById("profitDisplay").textContent = ${parseFloat(totalProfit).toFixed(8)} [${profitPercentage}%];
}

// Function to read and update total balance in real-time
function updateTotalBalance() {
    const balanceElement = document.getElementById("coin-value");
    if (balanceElement) {
        const balance = balanceElement.textContent.trim(); // Baca nilai balance dari elemen
        document.getElementById("totalBalanceDisplay").textContent = Total Balance: ${balance};
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
        // Update dashboard when balance element changes
        updateTotalBalance();
        updateProfitDisplay();
    });

    // Configure observer to monitor text changes
    observer.observe(balanceElement, {
        childList: true, // Monitor changes in child elements
        characterData: true, // Monitor character changes
        subtree: true, // Monitor all changes inside the element
    });

    // Perbarui balance pertama kali
    updateTotalBalance();
    updateProfitDisplay();
}

// Panggil fungsi observer
observeBalanceChanges();

// Function to check betting results
function checkResult(resultText) {
    const valueMatch = resultText.match(/-?\d+\.\d+/); // Capture the betting result numbers
    const value = valueMatch ? parseFloat(valueMatch[0]) : 0;
    console.log(value);
    if (resultText.includes("won") || resultText.includes("+")) {
        console.log(%cwin ${value} DOGE (hijau), "color: green; font-weight: bold;");
        const profit = value; // Profit = winning result
        totalProfit += profit; // Add to total profit
        totalBet += value / 2; // For example, bet marti-marti, count it as half
        updateProfitDisplay(); // Update dashboard
        updateChart("good", profit); // Grafik naik
        return "good";
    } else if (resultText.includes("lose") || resultText.includes("-")) {
        console.log(%close ${value} DOGE (merah), "color: red; font-weight: bold;");
        totalBet += Math.abs(value); // Add to total bet
        updateProfitDisplay(); // Update dashboard
        updateChart("bad", Math.abs(value)); // Graph down
        return "bad";
    }
    return null;
}

// The main function of the bot
async function startBot() {
    const actions = ["BetLowButton", "BetHighButton"];
    let currentActionIndex = 0; // Mulai dengan tombol Low

    // Initialize MutationObserver for betting results
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === "childList" || mutation.type === "characterData") {
                const resultElement = document.getElementById("LastBetInfoContainer");
                if (resultElement) {
                    const resultText = resultElement.textContent.trim().toLowerCase();
                    const result = checkResult(resultText);

                    if (result === "good") {
                        clickButton("BetReset"); // Click the Reset button
                        currentActionIndex = (currentActionIndex + 1) % actions.length; // Move to the next action
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

   // Execute button click with faster time
    while (true) {
        const action = actions[currentActionIndex];
        clickButton(action); // Click the Low or High button

        await new Promise((resolve) => setTimeout(resolve, 50)); // Delay hanya 1ms
    }
}

// Function to give click effect
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
        console.warn(Tombol "${buttonId}" tidak ditemukan.);
    }
}

// Jalankan bot
startBot();
