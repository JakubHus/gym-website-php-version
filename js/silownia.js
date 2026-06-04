const imageUrls = [
    'pic/gym1.webp',
    'pic/gym2.jpeg',
    'pic/gym3.webp',
    'pic/gym4.jpeg'
];

let currentImageIndex = 0;

const mainImage = document.querySelector('.main_container .main_image');

function changeImage() {
    mainImage.src = imageUrls[currentImageIndex];
    currentImageIndex = (currentImageIndex + 1) % imageUrls.length;
}

setInterval(changeImage, 10000);

changeImage();

let currentOpinionIndex = 0;
const opinions = document.querySelectorAll('.opinion_card');
const container = document.querySelector('.opinion_container');

function moveOpinions(direction) {
    const maxIndex = Math.ceil(opinions.length / 2) - 1;

    currentOpinionIndex += direction;
    if (currentOpinionIndex < 0) {
        currentOpinionIndex = maxIndex;
    } else if (currentOpinionIndex > maxIndex) {
        currentOpinionIndex = 0;
    }

    container.style.opacity = '0';
    container.style.transition = 'opacity 0.5s ease';

    setTimeout(() => {
        container.innerHTML = '';

        const start = currentOpinionIndex * 2;
        const end = start + 2;
        opinions.forEach((opinion, index) => {
            if (index >= start && index < end) {
                container.appendChild(opinion.cloneNode(true));
            }
        });

        container.style.opacity = '1';
    }, 500);
}
