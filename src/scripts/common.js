import '../styles/common.scss';
import { Chart, RadarController, RadialLinearScale, PointElement, LineElement, Filler, Tooltip } from 'chart.js';

Chart.register(RadarController, RadialLinearScale, PointElement, LineElement, Filler, Tooltip);

function initRadarCharts() {
  document.querySelectorAll('canvas[data-radar]').forEach((canvas) => {
    const data = JSON.parse(canvas.dataset.radar);
    new Chart(canvas, {
      type: 'radar',
      data: {
        labels: data.labels,
        datasets: [{
          data: data.values,
          backgroundColor: 'rgba(59, 130, 246, 0.25)',
          borderColor: 'rgba(37, 99, 235, 1)',
          pointBackgroundColor: 'rgba(37, 99, 235, 1)',
        }],
      },
      options: {
        scales: {
          r: {
            min: 0,
            max: 5,
            ticks: { stepSize: 1 },
          },
        },
        plugins: {
          legend: { display: false },
        },
      },
    });
  });
}

function initRegistrationWizard() {
  const form = document.getElementById('registration-form');
  if (!form) return;

  const steps = Array.from(form.querySelectorAll('.quiz-step'));
  const nextButton = document.getElementById('next-button');
  const progressFill = document.getElementById('progress-fill');
  const currentStepLabel = document.getElementById('current-step');
  const nameInput = form.querySelector('[name="name"]');
  const emailInput = form.querySelector('[name="email"]');
  const passwordInput = form.querySelector('[name="password"]');

  let currentStep = 0;

  function updateProgress() {
    currentStepLabel.textContent = String(currentStep + 1);
    progressFill.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
    nextButton.textContent = currentStep === steps.length - 1 ? '登録する' : '次へ';
  }

  function isCurrentQuestionAnswered() {
    const step = steps[currentStep];
    return step.querySelector('input[type="radio"]:checked') !== null;
  }

  nextButton.addEventListener('click', () => {
    if (nameInput.value.trim() === '' || emailInput.value.trim() === '' || passwordInput.value === '') {
      alert('名前・メールアドレス・パスワードを入力してください。');
      return;
    }
    if (!isCurrentQuestionAnswered()) {
      alert('回答を選択してください。');
      return;
    }

    if (currentStep === steps.length - 1) {
      form.submit();
      return;
    }

    steps[currentStep].classList.remove('active');
    currentStep += 1;
    steps[currentStep].classList.add('active');
    updateProgress();
  });

  updateProgress();
}

function initTeamModal() {
  const openButton = document.getElementById('open-team-modal');
  const closeButton = document.getElementById('close-team-modal');
  const modal = document.getElementById('team-modal');
  if (!openButton || !modal) return;

  openButton.addEventListener('click', () => modal.classList.add('active'));
  closeButton.addEventListener('click', () => modal.classList.remove('active'));
  modal.addEventListener('click', (event) => {
    if (event.target === modal) modal.classList.remove('active');
  });
}

function initInviteButton() {
  const inviteButton = document.getElementById('invite-member');
  if (!inviteButton) return;
  inviteButton.addEventListener('click', () => {
    alert('この機能は準備中です。');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initRadarCharts();
  initRegistrationWizard();
  initTeamModal();
  initInviteButton();
});
