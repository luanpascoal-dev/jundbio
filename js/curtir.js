function curtirPost(postId) {
    fetch('functions/curtir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.sucesso) {
            const likeButton = document.querySelector(`[data-post-id="${postId}"]`);
            const likeIcon = document.querySelector(`[data-icon="${postId}"]`);
            const likeCount = likeButton.querySelector(`[data-count-id="${postId}"]`);
            
            // Atualizar o ícone
            if (data.acao === 'curtir') {
                likeButton.classList.add('liked');
                likeIcon.classList.remove('fa-regular');
                likeIcon.classList.add('fa-solid');
            } else {
                likeButton.classList.remove('liked');
                likeIcon.classList.remove('fa-solid');
                likeIcon.classList.add('fa-regular');
            }
            
            // Atualizar o contador
            likeCount.textContent = data.total;
        } else {
            console.error('Erro ao processar curtida:', data.mensagem);
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
    });
}