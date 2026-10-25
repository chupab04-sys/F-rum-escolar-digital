<?php
/**
 * CEPS · Fórum Escolar — contadores de novidades por cartão
 *
 * Uso, em cada painel-*.php principal (depois de require 'config.php';):
 *
 *   require 'contadores.php';
 *   $novidades = obterContadores($pdo, $_SESSION['user_id'], 'professor');
 *
 * Depois, em cada cartão, mostre o número (se > 0):
 *   <?php if (($novidades['prof-avisos'] ?? 0) > 0): ?>
 *     <span class="cp-badge"><?= $novidades['prof-avisos'] ?></span>
 *   <?php endif; ?>
 */

function obterContadores(PDO $pdo, int $userId, string $perfil): array
{
    // Última visita a cada cartão deste utilizador (default = nunca visitado)
    $visitas = [];
    $stmt = $pdo->prepare('SELECT cartao, ultima_visita FROM paineis_visitas WHERE utilizador_id = ?');
    $stmt->execute([$userId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $visitas[$v['cartao']] = $v['ultima_visita'];
    }
    $nunca = '1970-01-01 00:00:00';
    $desde = fn($cartao) => $visitas[$cartao] ?? $nunca;

    $c = [];

    // --- PROFESSOR ---
    if ($perfil === 'professor') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM partilhas_pedagogicas WHERE autor_id != ? AND criado_em > ?');
        $stmt->execute([$userId, $desde('prof-partilha')]);
        $c['prof-partilha'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM comunicados c
             WHERE c.para_professores = 1
               AND c.id NOT IN (SELECT comunicado_id FROM comunicados_leituras WHERE utilizador_id = ?)"
        );
        $stmt->execute([$userId]);
        $naoLidos = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM agenda_eventos WHERE para_professores = 1 AND criado_em > ?');
        $stmt->execute([$desde('prof-avisos')]);
        $c['prof-avisos'] = $naoLidos + (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM duvidas d
             JOIN professor_turmas pt ON pt.id = d.professor_turma_id
             WHERE pt.professor_id = ? AND d.resposta IS NULL"
        );
        $stmt->execute([$userId]);
        $c['prof-duvidas'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM comunicados_respostas r
             JOIN comunicados co ON co.id = r.comunicado_id
             WHERE co.autor_id = ? AND co.autor_perfil = 'professor' AND r.criado_em > ?"
        );
        $stmt->execute([$userId, $desde('prof-contacto')]);
        $c['prof-contacto'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM debates WHERE criado_em > ?');
        $stmt->execute([$desde('prof-reunioes')]);
        $novosTemas = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM debates_respostas WHERE autor_id != ? AND criado_em > ?');
        $stmt->execute([$userId, $desde('prof-reunioes')]);
        $c['prof-reunioes'] = $novosTemas + (int)$stmt->fetchColumn();
    }

    // --- ALUNO ---
    if ($perfil === 'aluno') {
        $stmt = $pdo->prepare('SELECT turma FROM alunos_dados WHERE utilizador_id = ?');
        $stmt->execute([$userId]);
        $turma = $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM comunicados c
             WHERE c.para_alunos = 1
               AND (c.turma_alvo IS NULL OR c.turma_alvo = ?)
               AND c.id NOT IN (SELECT comunicado_id FROM comunicados_leituras WHERE utilizador_id = ?)"
        );
        $stmt->execute([$turma, $userId]);
        $c['aluno-oficial'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM agenda_eventos
             WHERE para_alunos = 1 AND (turma_alvo IS NULL OR turma_alvo = ?) AND criado_em > ?"
        );
        $stmt->execute([$turma, $desde('aluno-calendario')]);
        $eventosNovos = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM fichas f
             JOIN professor_turmas pt ON pt.id = f.professor_turma_id
             WHERE pt.turma = ? AND f.data_entrega IS NOT NULL AND f.criado_em > ?"
        );
        $stmt->execute([$turma, $desde('aluno-calendario')]);
        $c['aluno-calendario'] = $eventosNovos + (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM duvidas WHERE aluno_id = ? AND resposta IS NOT NULL AND respondido_em > ?'
        );
        $stmt->execute([$userId, $desde('aluno-duvidas')]);
        $c['aluno-duvidas'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM grupos_resumos r
             JOIN grupos_membros m ON m.grupo_id = r.grupo_id
             WHERE m.aluno_id = ? AND r.autor_id != ? AND r.criado_em > ?"
        );
        $stmt->execute([$userId, $userId, $desde('aluno-grupos')]);
        $c['aluno-grupos'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM gremio_temas WHERE criado_em > ?');
        $stmt->execute([$desde('aluno-gremio')]);
        $novosTemasGremio = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM gremio_respostas WHERE autor_id != ? AND criado_em > ?');
        $stmt->execute([$userId, $desde('aluno-gremio')]);
        $c['aluno-gremio'] = $novosTemasGremio + (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM fichas f
             JOIN professor_turmas pt ON pt.id = f.professor_turma_id
             WHERE pt.turma = ? AND f.criado_em > ?"
        );
        $stmt->execute([$turma, $desde('aluno-materiais')]);
        $c['aluno-materiais'] = (int)$stmt->fetchColumn();
    }

    // --- DIREÇÃO ---
    if ($perfil === 'direcao') {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM utilizadores WHERE perfil IN ('aluno','encarregado') AND criado_em > ?"
        );
        $stmt->execute([$desde('direcao-cadastros')]);
        $c['direcao-cadastros'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM debates_respostas WHERE criado_em > ?');
        $stmt->execute([$desde('direcao-reunioes')]);
        $c['direcao-reunioes'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM sugestoes WHERE lida = 0');
        $stmt->execute();
        $c['direcao-sugestoes'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM associacao_temas WHERE criado_em > ?');
        $stmt->execute([$desde('direcao-associacao')]);
        $novosTemasAssoc = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM associacao_respostas WHERE autor_id != ? AND criado_em > ?');
        $stmt->execute([$userId, $desde('direcao-associacao')]);
        $c['direcao-associacao'] = $novosTemasAssoc + (int)$stmt->fetchColumn();
    }

    // --- ENCARREGADO ---
    if ($perfil === 'encarregado') {
        $stmt = $pdo->prepare(
            "SELECT ad.turma, ed.aluno_utilizador_id
             FROM encarregados_dados ed
             JOIN alunos_dados ad ON ad.utilizador_id = ed.aluno_utilizador_id
             WHERE ed.utilizador_id = ?"
        );
        $stmt->execute([$userId]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        $turma = $dados['turma'] ?? null;
        $alunoId = $dados['aluno_utilizador_id'] ?? null;

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM comunicados c
             WHERE c.para_encarregados = 1
               AND (c.turma_alvo IS NULL OR c.turma_alvo = ?)
               AND (c.destinatario_id IS NULL OR c.destinatario_id = ?)
               AND c.id NOT IN (SELECT comunicado_id FROM comunicados_leituras WHERE utilizador_id = ?)"
        );
        $stmt->execute([$turma, $userId, $userId]);
        $c['enc-circulares'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM agenda_eventos
             WHERE para_encarregados = 1 AND (turma_alvo IS NULL OR turma_alvo = ?) AND criado_em > ?"
        );
        $stmt->execute([$turma, $desde('enc-calendario')]);
        $c['enc-calendario'] = (int)$stmt->fetchColumn();

        $c['enc-rendimento'] = 0;
        if ($alunoId) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM notas WHERE aluno_id = ? AND criado_em > ?');
            $stmt->execute([$alunoId, $desde('enc-rendimento')]);
            $c['enc-rendimento'] += (int)$stmt->fetchColumn();
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM associacao_temas WHERE criado_em > ?');
        $stmt->execute([$desde('enc-associacao')]);
        $novosTemasAssoc2 = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM associacao_respostas WHERE autor_id != ? AND criado_em > ?');
        $stmt->execute([$userId, $desde('enc-associacao')]);
        $c['enc-associacao'] = $novosTemasAssoc2 + (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM trocas_anuncios WHERE autor_id != ? AND criado_em > ?');
        $stmt->execute([$userId, $desde('enc-trocas')]);
        $c['enc-trocas'] = (int)$stmt->fetchColumn();
    }

    return $c;
}
