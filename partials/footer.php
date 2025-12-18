<footer class="footer">
  <div class="net-widget">
    <span class="net-label">Network:</span>
    <span id="net-readout">Detecting…</span>
  </div>
</footer>

<script>

  (function () {
    const readout = document.getElementById("net-readout");
    function getAutoNetworkProfile() {
      const c = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      if (!c) {
        return { label: "Unknown (API not available)", profile: "unknown" };
      }

      const parts = [];
      if (c.type) parts.push(`type=${c.type}`);
      if (c.effectiveType) parts.push(`effectiveType=${c.effectiveType}`);
      if (typeof c.downlink === "number") parts.push(`downlink≈${c.downlink}Mb/s`);
      if (typeof c.rtt === "number") parts.push(`rtt≈${c.rtt}ms`);
      if (c.saveData) parts.push(`saveData=on`);

      const profile = c.effectiveType || c.type || "unknown";
      return { label: parts.join(" • ") || "Unknown", profile };
    }

    function profileToCompression(profile) {
      if (profile === "slow-2g" || profile === "2g") return "LOW (360p-ish)";
      if (profile === "3g" || profile === "cellular") return "MEDIUM (720p-ish)";
      if (profile === "4g" || profile === "wifi" || profile === "ethernet") return "HIGH (1080p-ish)";
      return "MEDIUM (default)";
    }

    function render() {
      const auto = getAutoNetworkProfile();
      const compression = profileToCompression(auto.profile);
      readout.textContent =
        `Detected: ${auto.label} | Compression preset: ${compression}`;
      const hidden = document.getElementById("net-preset");
      if (hidden) hidden.value = preset;
    }

    const c = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (c && c.addEventListener) c.addEventListener("change", render);

    render();
  })();

</script>
