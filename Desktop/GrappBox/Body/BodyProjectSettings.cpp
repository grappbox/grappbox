#include "Body/BodyProjectSettings.h"

BodyProjectSettings::BodyProjectSettings(QWidget *parent) : QWidget(parent)
{
    //First we create the widgets and disable them (read mode)
    _api = API::SDataManager::GetCurrentDataConnector();
    _windowLayout = new QHBoxLayout(this);
    _mainLayout = new QVBoxLayout();
    _secondaryLayout = new QVBoxLayout();

    _basicProjectInformations = new QFormLayout();
    _socialInformations = new QFormLayout();
    _passwordInformations = new QFormLayout();
    _deleteRetreiveProject = new QHBoxLayout();
    _btnEditMode = new QPushButton(tr("Pass in edit mode"));
    _logo = new ImageUploadWidget();
    _projectName = new QLineEdit(PH_PROJECT_NAME);
    _projectDesc = new QTextEdit(PH_PROJECT_DESC);
    _companyName = new QLineEdit(PH_COMPANY_NAME);
    _contactPhone = new QLineEdit(PH_PROJECT_PHONE);
    _contactMail = new QLineEdit(PH_PROJECT_MAIL);
    _usersRoles = new RoleTableWidget();
    _facebook = new QLineEdit(PH_PROJECT_FACEBOOK);
    _twitter = new QLineEdit(PH_PROJECT_TWITTER);
    _password = new QLineEdit();
    _passwordConfirmation = new QLineEdit();
    _deleteProject = new QPushButton(tr("Delete the project"));
    _cancelDeletion = new QPushButton(tr("Retreive project from deletion"));
    _customerAccess = new QVBoxLayout();
    _createAccess = new QPushButton(tr("Create a new customer access"));
    _createAccessWindow = new CreateNewCustomerAccessWindow();

    SetWidgetsActive(false);
    _password->setEchoMode(QLineEdit::Password);
    _passwordConfirmation->setEchoMode(QLineEdit::Password);

    //Then we build the content layouts
    _basicProjectInformations->addWidget(_logo);
    _basicProjectInformations->addRow(new QLabel(tr("Project name : ")), _projectName);
    _basicProjectInformations->addRow(new QLabel(tr("Project description : ")), _projectDesc);
    _basicProjectInformations->addRow(new QLabel(tr("Company name : ")), _companyName);
    _basicProjectInformations->addRow(new QLabel(tr("Contact phone : ")), _contactPhone);
    _basicProjectInformations->addRow(new QLabel(tr("Contact mail : ")), _contactMail);
    _deleteRetreiveProject->addWidget(_deleteProject);
    _deleteRetreiveProject->addWidget(_cancelDeletion);
    _basicProjectInformations->addItem(_deleteRetreiveProject);

    _socialInformations->addRow(new QLabel(tr("Facebook link : ")), _facebook);
    _socialInformations->addRow(new QLabel(tr("Twitter link : ")), _twitter);

    _passwordInformations->addRow(new QLabel(tr("New Password : ")), _password);
    _passwordInformations->addRow(new QLabel(tr("Confirm Password : ")), _passwordConfirmation);

    //We connect the events
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));
    QObject::connect(_deleteProject, SIGNAL(released()), this, SLOT(DeleteProject()));
    QObject::connect(_cancelDeletion, SIGNAL(released()), this, SLOT(RetreiveProject()));
    QObject::connect(_createAccess, SIGNAL(released()), _createAccessWindow, SLOT(Open()));
    QObject::connect(_createAccessWindow, SIGNAL(CustomerCreationProcessEnd(QString)), this, SLOT(AccessCreated(QString)));

    //Finally we build all the layouts and widgets together
    _mainLayout->addWidget(_btnEditMode);
    _mainLayout->addWidget(new QLabel(tr("<h2>Basic Informations</h2>")));
    _mainLayout->addLayout(_basicProjectInformations);
    _mainLayout->addWidget(new QLabel(tr("<h2>Social Informations</h2>")));
    _mainLayout->addLayout(_socialInformations);
    _mainLayout->addWidget(new QLabel(tr("<h2>New password (fill only to modify it)</h2>")));
    _mainLayout->addLayout(_passwordInformations);
    _secondaryLayout->addWidget(new QLabel(tr("<h2>Users and roles (Direct modification)</h2>")));
    _secondaryLayout->addWidget(_usersRoles);
    _secondaryLayout->addWidget(new QLabel(tr("<h2>CustomerAccess</h2>")));
    _secondaryLayout->addWidget(_createAccess);
    _secondaryLayout->addLayout(_customerAccess);
    _windowLayout->addLayout(_mainLayout);
    _windowLayout->addLayout(_secondaryLayout);
    _windowLayout->setAlignment(_secondaryLayout,Qt::AlignTop);

}

BodyProjectSettings::~BodyProjectSettings()
{

}

void BodyProjectSettings::Show(int ID, MainWindow *mainApp)
{
    QVector<QString> data;

    _mainApplication = mainApp;
    _id = ID;
    _roleRequestNb = 0;
    data.append(API::SDataManager::GetDataManager()->GetToken());
    _api->Get(API::DP_PROJECT, API::GR_PROJECTS_USER, data, this, "GetSettingsSuccess", "FailureGetSettings");

}

void BodyProjectSettings::Hide()
{
    hide();
}

void BodyProjectSettings::SetWidgetsActive(bool active)
{
    _projectName->setEnabled(active);
    _projectDesc->setEnabled(active);
    _contactPhone->setEnabled(active);
    _companyName->setEnabled(active);
    _contactMail->setEnabled(active);
    _facebook->setEnabled(active);
    _twitter->setEnabled(active);
    _logo->setEnabled(active);
    _password->setEnabled(active);
    _passwordConfirmation->setEnabled(active);
}

void BodyProjectSettings::PassToEditMode()
{
    //We change the connection and text button
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));
    _btnEditMode->setText(tr("Save"));
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToStaticMode()));

    //Then we activate the widgets
    SetWidgetsActive(true);
}

void BodyProjectSettings::PassToStaticMode()
{
    QVector<QString> data;
    QString logo;
    QString logobits;

    //Disable the button and widgets
    _btnEditMode->setEnabled(false);
    SetWidgetsActive(false);

    //Then make the API call
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectID));
    data.append(_projectName->text() != PH_PROJECT_NAME ? _projectName->text() : "");
    data.append(_projectDesc->toPlainText() != PH_PROJECT_DESC ? _projectDesc->toPlainText() : "");
    if (_logo->isImageFromComputer())
    {
        logobits = _logo->getEncodedImage();
        data.append(logobits);
    }
    else
        data.append("");
    data.append(_contactPhone->text() != PH_PROJECT_PHONE ? _contactPhone->text() : "");
    data.append(_companyName->text() != PH_COMPANY_NAME ? _companyName->text() : "");
    data.append(_contactMail->text() != PH_PROJECT_MAIL ? _contactMail->text() : "");
    data.append(_facebook->text() != PH_PROJECT_FACEBOOK ? _facebook->text() : "");
    data.append(_twitter->text() != PH_PROJECT_FACEBOOK ? _facebook->text() : "");
    if (_password->text() != "")
    {
        if (_password->text() != _passwordConfirmation->text())
        {
            QMessageBox::critical(this, "Password error", "The password and the confirmation fields are different.");
            this->SetWidgetsActive(true);
            _btnEditMode->setEnabled(true);
            return;
        }
        data.append(_password->text());
    }
    else
        data.append("");
    _api->Put(API::DP_PROJECT, API::PUTR_ProjectSettings, data, this, "SetProjectSuccess", "Failure");
}

void BodyProjectSettings::Failure(int UNUSED id , QByteArray data)
{
    QMessageBox::critical(this, "Internal Error", "Fail to retreive data from internet");
    qDebug() << data;
}


void BodyProjectSettings::GetSettingsSuccess(int UNUSED id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object()["Project 1"].toObject();
    QVector<QString> dataRole;

    while (QLayoutItem *item = _customerAccess->takeAt(0))
    {
        if (item->widget())
            item->widget()->setParent(NULL);
        delete item;
    }

    _projectID = json["id"].toInt();
    if (!json["name"].isNull())
        _projectName->setText(json["name"].toString());
    if (!json["description"].isNull())
        _projectDesc->setText(json["description"].toString());
    if (!json["phone"].isNull())
        _contactPhone->setText(json["phone"].toString());
    if (!json["company"].isNull())
        _companyName->setText(json["company"].toString());
    if (!json["contact_mail"].isNull())
        _contactMail->setText(json["contact_mail"].toString());
    if (!json["facebook"].isNull())
        _facebook->setText(json["facebook"].toString());
    if (!json["twitter"].isNull())
        _twitter->setText(json["twitter"].toString());
    if (!json["logo"].isNull())
        _logo->setImage(json["logo"].toString());
    emit OnLoadingDone(_id);
    dataRole.append(API::SDataManager::GetDataManager()->GetToken());
    dataRole.append(QString::number(_projectID));
    _usersRoles->SetProjectId(_projectID);
    _usersRoles->Clear();
    _usersRoles->reset();

    _api->Get(API::DP_PROJECT, API::GR_PROJECT_ROLE, dataRole, this, "GetRolesSuccess", "Failure");
    _api->Get(API::DP_PROJECT, API::GR_PROJECT_USERS, dataRole, this, "GetUsersSuccess", "Failure");
    _api->Get(API::DP_PROJECT, API::GR_CUSTOMER_ACCESSES, dataRole, this, "GetCustomerAccessSuccess", "Failure");
}

void BodyProjectSettings::GetRolesSuccess(int UNUSED id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QJsonObject::iterator jIt;

    for (jIt = json.begin(); jIt != json.end(); jIt++)
    {
        QJsonObject current = (*jIt).toObject();
        _usersRoles->addRole(current["name"].toString(), current["id"].toInt());
    }
    ++_roleRequestNb;
    if (_roleRequestNb >= 2)
    {
        _usersRoles->reset();
        _usersRoles->refresh();
    }
}

void BodyProjectSettings::GetUsersSuccess(int UNUSED id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QJsonObject::iterator jIt;

    for (jIt = json.begin(); jIt != json.end(); jIt++)
    {
        QJsonObject current = (*jIt).toObject();
        _usersRoles->addUser(current["first_name"].toString() + " " + current["last_name"].toString(), current["user_id"].toInt());
    }
    ++_roleRequestNb;
    if (_roleRequestNb >= 2)
    {
        _usersRoles->reset();
        _usersRoles->refresh();
    }
}

void BodyProjectSettings::SetProjectSuccess(int, QByteArray)
{
    //Then reactivate the button and change the connexion
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToStaticMode()));
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));
    _btnEditMode->setText(tr("Pass in edit mode"));
    _btnEditMode->setEnabled(true);
}

void BodyProjectSettings::FailureGetSettings(int id, QByteArray data)
{
    QMessageBox::critical(this, tr("Security Level Error"), tr("Due to your security level, you can't access to project settings, contact your leader to know more about that problem."));
    Hide();
}

void BodyProjectSettings::DeleteProject()
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectID));

    _api->Delete(API::DP_PROJECT, API::DR_PROJECT, data, this, "DeleteProjectSuccess", "Failure");
}

void BodyProjectSettings::RetreiveProject()
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectID));
    _api->Get(API::DP_PROJECT, API::GR_PROJECT_CANCEL_DELETE, data, this, "RetreiveProjectSuccess", "Failure");
}

void BodyProjectSettings::DeleteProjectSuccess(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::information(this, tr("Project deleted"), tr("Project successfully deleted, you have 7 days to retreive it."));
}

void BodyProjectSettings::RetreiveProjectSuccess(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::information(this, tr("Project retreived"), tr("Project successfully retreived."));
}

void BodyProjectSettings::GetCustomerAccessSuccess(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object();
    QJsonObject::iterator it;

    for (it = obj.begin(); it != obj.end(); ++it)
    {
        QJsonObject currentAccess = (*it).toObject();
        CustomerAccessSettings *newAccess = new CustomerAccessSettings(currentAccess["name"].toString(), currentAccess["id"].toInt(), _projectID, currentAccess["customer_token"].toString());

        QObject::connect(newAccess, SIGNAL(Deleted(CustomerAccessSettings*)), this, SLOT(CustomerAccessDeleted(CustomerAccessSettings*)));
        _customerAccess->addWidget(newAccess);
    }
}

void BodyProjectSettings::CustomerAccessDeleted(CustomerAccessSettings *access)
{
    _customerAccess->removeWidget(access);
    delete access;
}

void BodyProjectSettings::AccessCreated(QString accessName)
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectID));
    data.append(accessName);
    _api->Post(API::DP_PROJECT, API::PR_CUSTOMER_GENERATE_ACCESS, data, this, "AccessCreatedSuccess", "Failure");
}

void BodyProjectSettings::AccessCreatedSuccess(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QVector<QString> params;

    _stackAccessID.append(json["id"].toInt());
    params.append(API::SDataManager::GetDataManager()->GetToken());
    params.append(QString::number(json["id"].toInt()));
    _api->Get(API::DP_PROJECT, API::GR_CUSTOMER_ACCESS_BY_ID, params, this, "GetOneCustomerAccessSuccess", "GetOneCustomerAccessFailure");
}

void BodyProjectSettings::GetOneCustomerAccessSuccess(int id, QByteArray data)
{
    QJsonObject json = QJsonDocument::fromJson(data).object();

    CustomerAccessSettings *newAccess = new CustomerAccessSettings(json["name"].toString(), _stackAccessID.first(), _projectID, json["customer_token"].toString());
    _stackAccessID.pop_front();
   _customerAccess->addWidget(newAccess);
}

void BodyProjectSettings::GetOneCustomerAccessFailure(int id, QByteArray data)
{
    _stackAccessID.pop_front();
    Failure(id, data);
}
