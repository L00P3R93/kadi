<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


?>

<meta name="game-token" content="<?php echo htmlspecialchars($_SESSION['g_id'] ?? ''); ?>">
<script>

const token = document.querySelector('meta[name="game-token"]').content;

window.GAME_AUTH = {
    token: token
};

console.log(window.GAME_AUTH);
</script>

<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='' xml:lang=''>
<head>
	<meta charset='utf-8' />
  <meta name="screen-orientation" content="landscape">
<meta name="x5-orientation" content="landscape">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, viewport-fit=cover">

<meta name="apple-mobile-web-app-capable" content="yes">

	<meta name='viewport' content='width=device-width, user-scalable=no' />
	<title>Kadi</title>
	<style type='text/css'>

		body {
			touch-action: none;
			margin: 0;
			border: 0 none;
			padding: 0;
			text-align: center;
			background-color: black;
		}

		#canvas {
			display: block;
			margin: 0;
			color: white;
		}

		#canvas:focus {
			outline: none;
		}

		.godot {
			font-family: 'Noto Sans', 'Droid Sans', Arial, sans-serif;
			color: #e0e0e0;
			background-color: #3b3943;
			background-image: linear-gradient(to bottom, #403e48, #35333c);
			border: 1px solid #45434e;
			box-shadow: 0 0 1px 1px #2f2d35;
		}


		/* Status display
		 * ============== */


		#status-progress {
			width: 366px;
			height: 7px;
			background-color: #38363A;
			border: 1px solid #444246;
			padding: 1px;
			box-shadow: 0 0 2px 1px #1B1C22;
			border-radius: 2px;
			visibility: visible;
		}

		@media only screen and (orientation:portrait) {
			#status-progress {
				width: 61.8%;
			}
		}

		#status-progress-inner {
			height: 100%;
			width: 0;
			box-sizing: border-box;
			transition: width 0.5s linear;
			background-color: #202020;
			border: 1px solid #222223;
			box-shadow: 0 0 1px 1px #27282E;
			border-radius: 3px;
		}

		#status-indeterminate {
			height: 42px;
			visibility: visible;
			position: relative;
		}

		#status-indeterminate > div {
			width: 4.5px;
			height: 0;
			border-style: solid;
			border-width: 9px 3px 0 3px;
			border-color: #2b2b2b transparent transparent transparent;
			transform-origin: center 21px;
			position: absolute;
		}

		#status-indeterminate > div:nth-child(1) { transform: rotate( 22.5deg); }
		#status-indeterminate > div:nth-child(2) { transform: rotate( 67.5deg); }
		#status-indeterminate > div:nth-child(3) { transform: rotate(112.5deg); }
		#status-indeterminate > div:nth-child(4) { transform: rotate(157.5deg); }
		#status-indeterminate > div:nth-child(5) { transform: rotate(202.5deg); }
		#status-indeterminate > div:nth-child(6) { transform: rotate(247.5deg); }
		#status-indeterminate > div:nth-child(7) { transform: rotate(292.5deg); }
		#status-indeterminate > div:nth-child(8) { transform: rotate(337.5deg); }

		#status-notice {
			margin: 0 100px;
			line-height: 1.3;
			visibility: visible;
			padding: 4px 6px;
			visibility: visible;
		}
.spinner {
  border: 6px solid transparent;        /* no background */
  border-top: 6px solid #ffffff;        /* visible top border */
  border-radius: 50%;
  width: 60px;                          /* slightly longer (was 50px) */
  height: 60px;
  animation: spin 1s linear infinite, colorShift 3s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* smoothly shift color around the spectrum */
@keyframes colorShift {
  0% { border-top-color: #fea724; }
  25% { border-top-color: #fea724; }
  50% { border-top-color: #fea724; }
  75% { border-top-color: #fea724; }
  100% { border-top-color: #fea724; }
}



#status {
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  display: none;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background: black;

}

#rotate {
  z-index: 9999 !important;
}


.screen {
  position: fixed;
  inset: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: black;
  color: white;
  z-index: 10;
}

.hidden {
  display: none !important;
}

/* Rotate screen animation */
#rotate { background: black; flex-direction: column; text-align: center; z-index: 20; }
.phone {
  position: relative;
  height: 120px;
  width: 60px;
  border: 2.5px solid #ccc; /* thinner outer frame */
  border-radius: 14px;
  background: radial-gradient(circle at center, #111 60%, #000 100%);
  box-shadow:
    0 0 6px rgba(255, 255, 255, 0.2),
    inset 0 0 2px rgba(255, 255, 255, 0.2); /* subtle edge reflection */
  animation: rotate 1.5s ease-in-out infinite alternate;
}

/* Top notch / speaker */
.phone::before {
  content: "";
  position: absolute;
  top: 5px;
  left: 50%;
  transform: translateX(-50%);
  width: 18px;
  height: 3px;
  border-radius: 2px;
  background: #ccc;
  opacity: 0.9;
}

/* Home button */
.phone .home-btn {
  position: absolute;
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
  width: 8px;
  height: 8px;
  border: 1.2px solid #bbb;
  border-radius: 50%;
  box-shadow:
    inset 0 0 2px rgba(255,255,255,0.2),
    0 0 2px rgba(255,255,255,0.15);
  opacity: 0.9;
}


/* home button */
.phone .home-btn {
  position: absolute;
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
  width: 10px;
  height: 10px;
  border: 1.5px solid #aaa;
  border-radius: 50%;
  box-shadow: inset 0 0 3px rgba(255,255,255,0.2), 0 0 2px rgba(255,255,255,0.1);
  opacity: 0.9;
}



@keyframes rotate {
  0% { transform: rotate(0); }
  50%, 100% { transform: rotate(-90deg); }
}
.message {
  margin-top: 20px;
  font-family: "Courier New", monospace;
  font-size: 1.2em;
}

#fsBox:hover {
  transform: scale(1.05);
  background: rgba(40,40,40,0.9);
  cursor: pointer;
}


/* === Fullscreen Prompt: Pure black, uniform background === */
#fullscreenPrompt {
  position: fixed;
  font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: #000; /* solid black */
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  color: #fff;
  text-align: center;
  z-index: 9999;
  transition: opacity 0.6s ease;
}

#fullscreenPrompt.hidden {
  opacity: 0;
  pointer-events: none;
}

#fullscreenPrompt .prompt-content {
  background: none; /* remove the grey box */
  padding: 0;
  border-radius: 0;
}

#fullscreenPrompt h2 {
  font-size: 1.8em;
  margin-bottom: 10px;
  font-weight: 700;
}

#fullscreenPrompt p {
  font-size: 1em;
  margin-bottom: 20px;
  color: #ccc;
}


#installBtn {
  background: #ffb800;
  border: none;
  color: #000;
  font-weight: 600;
  font-size: 1.1em;
  padding: 12px 24px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

#installBtn :hover {
  background: #ffc933;
  box-shadow: 0 0 12px #ffb800;
  transform: scale(1.01);
}


/* Dark grey for iOS add-to-home option */
#iosBtn {
  background: #ffb800;
  border: none;
  color: #000;
  font-weight: 600;
  font-size: 1.1em;
  padding: 12px 24px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

#iosBtn :hover {
  background: #ffc933;
  box-shadow: 0 0 12px #ffb800;
  transform: scale(1.01);
}


#fullscreenBtn {
  background: #ffb800;
  border: none;
  color: #000;
  font-weight: 600;
  font-size: 1.1em;
  padding: 12px 24px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

#fullscreenBtn:hover {
  background: #ffc933;
  box-shadow: 0 0 12px #ffb800;
  transform: scale(1.01);
}

/* Device body - landscape */
.device {
  position: relative;
  width: 120px;
  height: 60px;
  border: 2.5px solid #ccc;
  border-radius: 14px;
  background: #000;
 
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  margin: 0 auto 20px; /* centers device above text */
}

/* Inner screen */
.device .screen {
  position: relative;
  width: 85%;
  height: 73.666%;
  border-radius: 6px;
  background: #000;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #eee;
  font-family: "Courier New", monospace;
  font-size: 1.2rem;
  animation: expandShrink 4s ease-in-out infinite;
}




	</style>





<link id='-gd-engine-icon' rel='icon' type='image/png' href='index.icon.png' />
<link rel='apple-touch-icon' href='index.apple-touch-icon.png'/>
<link rel='manifest' href='index.manifest.json'>

</head>
<body>
	<canvas id='canvas'>
		HTML5 canvas appears to be unsupported in the current browser.<br />
		Please try updating or use a different browser.
	</canvas>
	
	<div id="status">
	  <div style="display: flex; flex-direction: column; align-items: center;">
	    <div id="spinner" class="spinner"></div><br>
	    <div id="loading-text" style="color:white; margin-top:10px; font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; font-size:20px;">Loading 0%</div>
	  </div>
	  <div id="status-notice" class="godot" style="display:none;"></div>
	</div>

	<!-- Rotate screen -->
<div id="rotate" class="screen hidden">
  <div class="phone">
  <div class="home-btn"></div>
</div>

  <br><br>
  <div class="message">Please rotate your device!</div>
</div>
  

<!-- Fullscreen prompt -->
<div id="fullscreenPrompt" class="hidden">
  <div class="prompt-content">

    <!-- Device animation inside fullscreenPrompt -->
    <!--<div class="device">
      <div class="screen"></div>
    </div>-->

    <h2>Your Quest Awaits</h2>
    <p>For the ultimate Kadi Kings experience, go fullscreen and claim your throne</p>
	<button id="installBtn" style="margin-top:10px;">Install Web App</button>
    <button id="iosBtn" style="margin-top:10px;">Add to Home Screen (iOS)</button>
    <button id="fullscreenBtn">Enter Fullscreen</button>
  </div>
</div>



	<script type='text/javascript' src='index.js'></script>


<script>
if ('serviceWorker' in navigator) {
    fetch('version.php')
    .then(r => r.text())
    .then(version => {
        version = version.trim();

        navigator.serviceWorker.register('index.service.worker.js?v=' + encodeURIComponent(version))
        .then(reg => {
            if (reg.waiting) {
                reg.waiting.postMessage("update");
            }
        });

        localStorage.setItem("kadi_version", version);
    })
    .catch(err => console.error("Version fetch failed:", err));
}
</script>


	<script type="text/javascript">
let gameStarted = false;

const GODOT_CONFIG = {
  "args": [],
  "canvasResizePolicy": 2,
  "executable": "index",
  "experimentalVK": true,
  "fileSizes": { "index.pck": 54118128, "index.wasm": 17865444 },
  "focusCanvas": true,
  "gdnativeLibs": [],
  "serviceWorker": ""
};


var engine = new Engine(GODOT_CONFIG);

// === Helpers ===
function isLandscape2() {
  return window.innerWidth > window.innerHeight;
}

function isLandscape() {
  // iOS Safari sometimes reports wrong window.innerWidth early.
  // Use orientation media query as the main check.
  if (window.matchMedia("(orientation: landscape)").matches) return true;
  // Fallback for other browsers
  return window.innerWidth > window.innerHeight;
}

function isStandalone() {
  return (
    window.matchMedia('(display-mode: standalone)').matches || // Android/modern browsers
    window.navigator.standalone === true // iOS Safari
  );
}


function isFullscreen() {
  return (
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.msFullscreenElement
  );
}

// === UI states ===
function showRotateMessage() {
  // Keep loader hidden while waiting for user to rotate
  const loadingText = document.getElementById("loading-text");
  loadingText.textContent = "Please rotate your device";

  document.getElementById("fullscreenPrompt").classList.add("hidden");
  document.getElementById("rotate").classList.remove("hidden");
}


function showFullscreenPrompt() {
  document.getElementById("rotate").classList.add("hidden");
  //document.getElementById("status").style.display = "none";

  document.getElementById("fullscreenPrompt").classList.remove("hidden");
}

function hideAllScreens() {
  document.getElementById("rotate").classList.add("hidden");
  document.getElementById("fullscreenPrompt").classList.add("hidden");
  //document.getElementById("status").style.display = "none";
}

// === Fullscreen logic ===
function ensureFullscreen(callback) {
  const prompt = document.getElementById("fullscreenPrompt");
  const button = document.getElementById("fullscreenBtn");

  function showPrompt() {
    prompt.classList.remove("hidden");
  }

  function hidePrompt() {
    prompt.classList.add("hidden");
  }

  function tryFullscreen() {
    const elem = document.documentElement;
    if (elem.requestFullscreen) elem.requestFullscreen();
    else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
    else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
  }

  document.addEventListener("fullscreenchange", () => {
    if (isFullscreen()) {
      hidePrompt();
      if (callback) callback();
    } else {
      showPrompt();
    }
  });

  if (isFullscreen()) {
    hidePrompt();
    callback();
  } else {
    showPrompt();
    tryFullscreen();  // ✅ <-- THIS WAS MISSING
  }

  button.onclick = tryFullscreen;
  document.addEventListener("dblclick", tryFullscreen);
}

function startGame() {
  if (gameStarted) return;
  gameStarted = true;

  // ✅ Make sure only the loader is visible while loading
  hideAllScreens();
  const loader = document.getElementById("status");
  loader.style.display = "flex";

  engine.startGame({
    onProgress: function (current, total) {
      const loadingText = document.getElementById("loading-text");
      if (total > 0) {
        let percent = Math.floor((current / total) * 100);
        loadingText.textContent = `Loading ${percent}%`;
      } else {
        loadingText.textContent = "Loading...";
      }
    },
  }).then(() => {
    // ✅ Game is ready → remove loader entirely
    loader.remove();
    document.getElementById("canvas").style.display = "block"; // ensure canvas visible
  }).catch((err) => {
    console.error(err);
    document.getElementById("loading-text").textContent = err.message || err;
  });
}



// === Main logic (fixed) ===
window.addEventListener("load", () => {
  // 1️⃣ If NOT landscape → only show rotate screen, do nothing else


  // ? Skip fullscreen prompt if app is already installed
  if (isStandalone()) {
    document.getElementById("fullscreenPrompt").classList.add("hidden");
  } else {
    document.getElementById("fullscreenPrompt").classList.remove("hidden");
  }

  if (!isLandscape()) {
    showRotateMessage();

    const waitForLandscape = () => {
      if (isLandscape()) {
        window.removeEventListener("resize", waitForLandscape);
        requestFullscreenAndStart();
      }
    };

    window.addEventListener("resize", waitForLandscape);
    return; // ✅ prevents any fullscreen/game start in portrait
  }

  // 2️⃣ If already in landscape, go fullscreen and start
  requestFullscreenAndStart();
});

function requestFullscreenAndStart() {
  ensureFullscreen(() => {
    hideAllScreens();
    startGame();
  });
}





// === Respond to rotation or fullscreen exit ===
window.addEventListener("resize", () => handleStateChange());
document.addEventListener("fullscreenchange", () => handleStateChange());

function handleStateChange() {
  const rotate = document.getElementById("rotate");
  const fullscreenPrompt = document.getElementById("fullscreenPrompt");
  const loader = document.getElementById("status");

  if (!isIos() && !isFullscreen()) {
    fullscreenPrompt.classList.remove("hidden");
    rotate.classList.add("hidden");
    return;
  }

  if (!isLandscape()) {
    rotate.classList.remove("hidden");
    fullscreenPrompt.classList.add("hidden");
    return;
  }

  // Landscape + fullscreen
  rotate.classList.add("hidden");
  fullscreenPrompt.classList.add("hidden");

  if (!gameStarted) {
    startGame();
  } else {
    // just leave things as they are
  }
}


</script>



<script>
let deferredPrompt;
const installBtn = document.getElementById("installBtn");
const iosBtn = document.getElementById("iosBtn");

function isIos() {
  return /iphone|ipad|ipod/i.test(navigator.userAgent);
}
function isStandalone() {
  return window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone;
}

window.addEventListener("beforeinstallprompt", e => {
  e.preventDefault();
  deferredPrompt = e;
  installBtn.style.display = "inline-block";
});

installBtn.addEventListener("click", async () => {
  if (isStandalone()) {
    alert("Kadi Kings is already installed.");
    return;
  }
  if (deferredPrompt) {
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
  } else {
    alert("Install not available on this browser.");
  }
});

iosBtn.addEventListener("click", () => {
  if (isStandalone()) {
    alert("Kadi Kings is already installed.");
    return;
  }
  if (isIos()) {
    alert('To install: Tap the Share icon ? then �Add to Home Screen�.');
  } else {
    alert("This option is only for iPhone or iPad.");
  }
});

// hide irrelevant button
if (isIos()) {
  installBtn.style.display = "none";
} else {
  iosBtn.style.display = "none";
}
</script>



<script>
const fullscreenBtn = document.getElementById("fullscreenBtn");

function isIos() {
  return /iphone|ipad|ipod/i.test(navigator.userAgent);
}



if (isIos()) {
  // iPhone/iPad ? skip fullscreen
  fullscreenBtn.textContent = "Play";
  fullscreenBtn.addEventListener("click", () => {
    document.getElementById("canvas").style.display = "block"; 
    document.getElementById("fullscreenPrompt").classList.add("hidden");
     document.getElementById("rotate").classList.add("hidden");
    document.getElementById("status").style.display = "flex"; // show loader
    hideAllScreens();
    if (!gameStarted) {
      //alert(1);
      //gameStarted = true;
      startGame();
    } else {
    // just leave things as they are
    }

    //startGame();
  });
} else {
  // Android/Desktop ? normal fullscreen
  fullscreenBtn.addEventListener("click", () => {
    const elem = document.documentElement;
    if (elem.requestFullscreen) elem.requestFullscreen();
    else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
    else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
  });
}
</script>



</body>
</html>

