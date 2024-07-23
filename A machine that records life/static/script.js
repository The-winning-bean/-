document.addEventListener('DOMContentLoaded', function() {
    loadSections();
});

document.getElementById('create-section-form').addEventListener('submit', function(e) {
    e.preventDefault();

    console.log('Form submitted');  // 添加日志

    const form = document.getElementById('create-section-form');
    const formData = new FormData(form);

    fetch('create_section.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received');  // 添加日志
        return response.json();
    })
    .then(data => {
        console.log('Data:', data);  // 添加日志
        if (data.status === 'success') {
            alert(data.message);
            addSectionToPage(data);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

function loadSections() {
    fetch('get_sections.php')
        .then(response => response.json())
        .then(data => {
            const sectionsDiv = document.getElementById('sections');
            sectionsDiv.innerHTML = '';

            data.forEach(section => {
                addSectionToPage(section);
            });
        });
}

function addSectionToPage(section) {
    const sectionsDiv = document.getElementById('sections');

    const newSection = document.createElement('div');
    newSection.className = 'section';
    newSection.id = `section-${section.id}`;

    const link = document.createElement('a');
    link.href = section.url;

    const img = document.createElement('img');
    img.src = section.cover;
    img.alt = '封面';

    const p = document.createElement('p');
    p.textContent = section.title;

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'delete-btn';
    deleteBtn.textContent = '删除';
    deleteBtn.onclick = function() {
        deleteSection(section.id);
    };

    link.appendChild(img);
    link.appendChild(p);

    newSection.appendChild(link);
    newSection.appendChild(deleteBtn);

    sectionsDiv.appendChild(newSection);
}

function deleteSection(sectionId) {
    fetch('delete_section.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${sectionId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            loadSections();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
