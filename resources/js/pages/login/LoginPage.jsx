import React, { useState } from "react";

const LoginPage = () => {
  const [formData, setFormData] = useState({
    email: "",
    password: "",
    remember: false,
  });

  const [showPassword, setShowPassword] = useState(false);

  const floatingBoxes = [
    "left-[8%] top-[12%] h-14 w-14 animation-delay-0",
    "left-[18%] bottom-[18%] h-10 w-10 animation-delay-1000",
    "right-[12%] top-[16%] h-16 w-16 animation-delay-2000",
    "right-[20%] bottom-[14%] h-12 w-12 animation-delay-3000",
    "left-[50%] top-[8%] h-8 w-8 animation-delay-1500",
    "right-[42%] bottom-[8%] h-9 w-9 animation-delay-2500",
  ];

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;

    setFormData((prev) => ({
      ...prev,
      [name]: type === "checkbox" ? checked : value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    console.log("Data login:", formData);
    alert("Login berhasil diklik!");
  };

  return (
    <>
      <style>
        {`
          @keyframes fadeUp3D {
            from {
              opacity: 0;
              transform: translateY(30px) rotateX(12deg) scale(0.96);
            }
            to {
              opacity: 1;
              transform: translateY(0) rotateX(0deg) scale(1);
            }
          }

          @keyframes floatBox {
            0%, 100% {
              transform: translate3d(0, 0, 0) rotateX(22deg) rotateY(-24deg) rotateZ(0deg);
            }
            50% {
              transform: translate3d(0, -22px, 26px) rotateX(35deg) rotateY(-12deg) rotateZ(12deg);
            }
          }

          @keyframes cardFloat {
            0%, 100% {
              transform: perspective(1100px) rotateX(0deg) rotateY(0deg) translateY(0);
            }
            50% {
              transform: perspective(1100px) rotateX(1.5deg) rotateY(-1.5deg) translateY(-6px);
            }
          }

          @keyframes glowPulse {
            0%, 100% {
              box-shadow:
                0 28px 80px rgba(0, 0, 0, 0.45),
                0 0 30px rgba(20, 184, 166, 0.22),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            }
            50% {
              box-shadow:
                0 34px 100px rgba(0, 0, 0, 0.55),
                0 0 70px rgba(20, 184, 166, 0.42),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            }
          }

          @keyframes shineMove {
            0% {
              transform: translateX(-120%) rotate(18deg);
            }
            100% {
              transform: translateX(220%) rotate(18deg);
            }
          }

          .login-3d-enter {
            animation: fadeUp3D 0.85s ease forwards;
          }

          .login-3d-card {
            animation: cardFloat 5s ease-in-out infinite, glowPulse 3.5s ease-in-out infinite;
            transform-style: preserve-3d;
          }

          .floating-box {
            animation: floatBox 6s ease-in-out infinite;
            transform-style: preserve-3d;
          }

          .animation-delay-0 {
            animation-delay: 0s;
          }

          .animation-delay-1000 {
            animation-delay: 1s;
          }

          .animation-delay-1500 {
            animation-delay: 1.5s;
          }

          .animation-delay-2000 {
            animation-delay: 2s;
          }

          .animation-delay-2500 {
            animation-delay: 2.5s;
          }

          .animation-delay-3000 {
            animation-delay: 3s;
          }

          .login-shine::before {
            content: "";
            position: absolute;
            inset: -40%;
            width: 45%;
            background: linear-gradient(
              90deg,
              transparent,
              rgba(255, 255, 255, 0.16),
              transparent
            );
            animation: shineMove 5s ease-in-out infinite;
          }
        `}
      </style>

      <div className="relative flex h-screen items-center justify-center overflow-hidden bg-[#050817] px-4 text-white">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.16),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(6,182,212,0.18),transparent_34%)]" />
        <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.035)_1px,transparent_1px)] bg-[size:44px_44px] opacity-30" />

        <div className="absolute -left-32 top-10 h-72 w-72 rounded-full bg-teal-400/20 blur-3xl" />
        <div className="absolute -right-32 bottom-0 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl" />

        {floatingBoxes.map((box, index) => (
          <div
            key={index}
            className={`floating-box absolute ${box} rounded-2xl border border-teal-300/20 bg-teal-300/10 shadow-2xl shadow-teal-500/10 backdrop-blur-xl`}
          >
            <div className="absolute inset-1 rounded-xl border border-white/10 bg-white/5" />
            <div className="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-teal-300 shadow-lg shadow-teal-300/50" />
          </div>
        ))}

        <div className="login-3d-enter relative z-10 w-full max-w-[420px]">
          <div className="mb-5 text-center">
            <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-400 text-lg font-black text-[#050817] shadow-[0_18px_45px_rgba(20,184,166,0.35)]">
              SR
            </div>

            <p className="tracking-[0.32em] text-[11px] font-black text-teal-300">
              ADMIN PANEL
            </p>

            <h1 className="mt-1 text-3xl font-black text-white">
              Sirekrut
            </h1>
          </div>

          <div className="login-3d-card login-shine relative overflow-hidden rounded-[1.8rem] border border-white/10 bg-white/[0.08] p-2 backdrop-blur-2xl">
            <div className="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-teal-300/20 blur-2xl" />
            <div className="absolute -bottom-8 -left-8 h-28 w-28 rounded-full bg-cyan-300/10 blur-2xl" />

            <div className="relative rounded-[1.45rem] border border-white/10 bg-[#080d20]/95 p-6 sm:p-7">
              <div className="mb-6">
                <div className="mb-3 inline-flex rounded-full border border-teal-300/20 bg-teal-300/10 px-4 py-2">
                  <span className="text-[11px] font-black uppercase tracking-[0.28em] text-teal-300">
                    Login Admin
                  </span>
                </div>

                <h2 className="text-2xl font-black text-white">
                  Selamat Datang
                </h2>

                <p className="mt-2 text-sm leading-6 text-slate-400">
                  Masukkan akun admin untuk membuka sistem Sirekrut.
                </p>
              </div>

              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label
                    htmlFor="email"
                    className="mb-2 block text-sm font-bold text-slate-300"
                  >
                    Email
                  </label>

                  <div className="relative">
                    <div className="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-teal-300">
                      ✉
                    </div>

                    <input
                      id="email"
                      type="email"
                      name="email"
                      placeholder="admin@email.com"
                      value={formData.email}
                      onChange={handleChange}
                      required
                      className="h-12 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-11 text-sm text-white outline-none transition duration-300 placeholder:text-slate-500 focus:border-teal-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-teal-300/10"
                    />
                  </div>
                </div>

                <div>
                  <label
                    htmlFor="password"
                    className="mb-2 block text-sm font-bold text-slate-300"
                  >
                    Password
                  </label>

                  <div className="relative">
                    <div className="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-teal-300">
                      🔒
                    </div>

                    <input
                      id="password"
                      type={showPassword ? "text" : "password"}
                      name="password"
                      placeholder="Masukkan password"
                      value={formData.password}
                      onChange={handleChange}
                      required
                      className="h-12 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-11 pr-20 text-sm text-white outline-none transition duration-300 placeholder:text-slate-500 focus:border-teal-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-teal-300/10"
                    />

                    <button
                      type="button"
                      onClick={() => setShowPassword((prev) => !prev)}
                      className="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-teal-300/10 px-3 py-2 text-[11px] font-black text-teal-300 transition duration-300 hover:bg-teal-300 hover:text-[#050817]"
                    >
                      {showPassword ? "Hide" : "Show"}
                    </button>
                  </div>
                </div>

                <div className="flex items-center justify-between gap-3 text-sm">
                  <label className="flex cursor-pointer items-center gap-2 text-slate-400">
                    <input
                      type="checkbox"
                      name="remember"
                      checked={formData.remember}
                      onChange={handleChange}
                      className="h-4 w-4 rounded border-white/20 bg-white/10 text-teal-400 focus:ring-teal-400"
                    />
                    <span>Ingat saya</span>
                  </label>

                  <a
                    href="/forgot-password"
                    className="font-bold text-teal-300 transition hover:text-teal-200"
                  >
                    Lupa password?
                  </a>
                </div>

                <button
                  type="submit"
                  className="group relative h-12 w-full overflow-hidden rounded-2xl bg-teal-400 text-sm font-black uppercase tracking-[0.25em] text-[#050817] shadow-lg shadow-teal-400/25 transition duration-300 hover:-translate-y-0.5 hover:bg-teal-300 hover:shadow-2xl hover:shadow-teal-400/40 active:translate-y-0"
                >
                  <span className="relative z-10">Masuk</span>
                  <span className="absolute inset-y-0 -left-20 w-16 rotate-12 bg-white/40 transition duration-700 group-hover:left-[120%]" />
                </button>
              </form>

              <div className="mt-5 text-center text-sm text-slate-400">
                <p>
                  Belum punya akun?{" "}
                  <a
                    href="/register"
                    className="font-bold text-teal-300 transition hover:text-teal-200"
                  >
                    Hubungi admin
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default LoginPage;