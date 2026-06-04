document.addEventListener("DOMContentLoaded", () => {

    const idioma = localStorage.getItem("idiomaSistema") || "pt-BR";

    if (idioma !== "en") {
        return;
    }

    const traducoes = {

        "Início": "Home",
        "Página Inicial": "Homepage",
        "Dashboard": "Dashboard",
        "Painel": "Dashboard",
        "Sistema": "System",
        "Empresa": "Company",
        "Funcionário": "Employee",
        "Funcionários": "Employees",
        "Usuário": "User",
        "Usuários": "Users",
        "Departamento": "Department",
        "Cargo": "Position",
        "Perfil": "Profile",
        "Meu perfil": "My Profile",

        "Ponto": "Time Clock",
        "Histórico de ponto": "Time History",
        "Registro de ponto": "Time Registration",
        "Banco de horas": "Hour Bank",
        "Férias": "Vacations",
        "Licenças médicas": "Medical Leaves",
        "Solicitações": "Requests",
        "Solicitações e licenças": "Requests and Leaves",
        "Comunicados": "Announcements",
        "Emergências": "Emergencies",
        "Holerite": "Payslip",

        "Cadastrar": "Register",
        "Cadastrar usuário": "Create User",
        "Cadastrar funcionário": "Create Employee",
        "Editar": "Edit",
        "Excluir": "Delete",
        "Salvar": "Save",
        "Salvar Alterações": "Save Changes",
        "Cancelar": "Cancel",
        "Atualizar": "Update",
        "Importar": "Import",
        "Exportar": "Export",
        "Pesquisar": "Search",
        "Buscar": "Search",
        "Filtrar": "Filter",
        "Limpar": "Clear",
        "Enviar": "Send",
        "Confirmar": "Confirm",
        "Voltar": "Back",

        "Importar Funcionários": "Import Employees",
        "Importar banco": "Import Hour Bank",
        "Importar pontos": "Import Time Records",
        "Arquivo CSV": "CSV File",
        "Selecione um arquivo": "Select a file",
        "Enviar planilha": "Upload Spreadsheet",

        "Ativo": "Active",
        "Inativo": "Inactive",
        "Pendente": "Pending",
        "Aprovado": "Approved",
        "Rejeitado": "Rejected",
        "Concluído": "Completed",
        "Completo": "Complete",
        "Em andamento": "In Progress",
        "Ausente": "Absent",
        "Atraso": "Late",

        "Configurações": "Settings",
        "Segurança": "Security",
        "Notificações": "Notifications",
        "Aparência": "Appearance",
        "Tema": "Theme",
        "Idioma": "Language",
        "Claro": "Light",
        "Escuro": "Dark",
        "Permissões": "Permissions",

        "Entrar": "Login",
        "Login": "Login",
        "Senha": "Password",
        "E-mail": "Email",
        "Esqueci minha senha": "Forgot my password",
        "Criar conta": "Create account",
        "Cadastre sua empresa": "Register your company",

        "Razão Social": "Corporate Name",
        "Nome Fantasia": "Trade Name",
        "CNPJ": "Company Registration Number",
        "Telefone": "Phone",
        "Cidade": "City",
        "Estado": "State",
        "Endereço": "Address",
        "Plano": "Plan",

        "Plano Pequeno Porte": "Small Business Plan",
        "Plano Médio Porte": "Medium Business Plan",
        "Forma de Pagamento": "Payment Method",
        "Confirmar Pagamento": "Confirm Payment",

        "Total de funcionários": "Total Employees",
        "Horas trabalhadas": "Hours Worked",
        "Solicitações pendentes": "Pending Requests",
        "Atividades recentes": "Recent Activities",

        "Digite seu nome": "Enter your name",
        "Digite seu email": "Enter your email",
        "Digite sua senha": "Enter your password",
        "Buscar por nome, e-mail, cargo ou departamento...": "Search by name, email, position or department..."
    };

    function traduzirTexto(node) {

        if (node.nodeType === Node.TEXT_NODE) {

            const texto = node.nodeValue.trim();

            if (traducoes[texto]) {
                node.nodeValue = node.nodeValue.replace(texto, traducoes[texto]);
            }

        }

    }

    function percorrer(elemento) {

        if (
            elemento.tagName === "SCRIPT" ||
            elemento.tagName === "STYLE" ||
            elemento.tagName === "TEXTAREA"
        ) {
            return;
        }

        elemento.childNodes.forEach((node) => {

            if (node.nodeType === Node.TEXT_NODE) {
                traduzirTexto(node);
            }

            if (node.nodeType === Node.ELEMENT_NODE) {
                percorrer(node);
            }

        });

    }

    percorrer(document.body);

    document.querySelectorAll("input, textarea").forEach(campo => {

        const ph = campo.getAttribute("placeholder");

        if (ph && traducoes[ph]) {
            campo.setAttribute("placeholder", traducoes[ph]);
        }

    });

    document.querySelectorAll("option").forEach(option => {

        const txt = option.textContent.trim();

        if (traducoes[txt]) {
            option.textContent = traducoes[txt];
        }

    });

});