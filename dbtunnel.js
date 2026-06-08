import { spawn } from "child_process";
import os from "os";
import path from "path";
import dotenv from "dotenv";

dotenv.config();

const {
  SSH_KEY_NAME,
  SSH_USER,
  SSH_HOST,
  SSH_PORT = "22",
  DB_TUNNEL_LOCAL_PORT = "15432",
} = process.env;

if (!SSH_KEY_NAME || !SSH_USER || !SSH_HOST) {
  console.error("❌ Missing SSH env variables");
  console.error("Required: SSH_KEY_NAME, SSH_USER, SSH_HOST");
  process.exit(1);
}

const SSH_KEY_PATH = path.join(os.homedir(), ".ssh", SSH_KEY_NAME);

const args = [
  "-i",
  SSH_KEY_PATH,
  "-p",
  SSH_PORT,
  "-N",
  "-o",
  "ServerAliveInterval=60",
  "-o",
  "ServerAliveCountMax=3",
  "-o",
  "TCPKeepAlive=yes",
  "-L",
  `${DB_TUNNEL_LOCAL_PORT}:127.0.0.1:5432`,
  `${SSH_USER}@${SSH_HOST}`,
];

console.log("🚇 Opening PostgreSQL SSH tunnel");
console.log("🔑 SSH key :", SSH_KEY_PATH);
console.log("📍 Local  :", `127.0.0.1:${DB_TUNNEL_LOCAL_PORT}`);
console.log("🗄️ Remote :", "127.0.0.1:5432");

const ssh = spawn("ssh", args, { stdio: "inherit" });

ssh.on("exit", (code) => {
  console.log(`❌ Tunnel closed (exit ${code})`);
});