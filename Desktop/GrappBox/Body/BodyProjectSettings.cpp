#include "Body/BodyProjectSettings.h"

BodyProjectSettings::BodyProjectSettings(QWidget *parent) : QWidget(parent)
{
    //First we create the widgets and disable them (read mode)
    _api = API::SDataManager::GetCurrentDataConnector();
    _mainLayout = new QVBoxLayout(this);
    _basicProjectInformations = new QFormLayout();
    _socialInformations = new QFormLayout();
    _btnEditMode = new QPushButton(tr("Pass in edit mode"));
    _logo = new ImageUploadWidget();
    _projectName = new QLineEdit(tr("Enter your project name here..."));
    _projectDesc = new QTextEdit(tr("Enter your project description here..."));
    _companyName = new QLineEdit(tr("Enter the company who order the project..."));
    _contactPhone = new QLineEdit(tr("Enter the company phone here..."));
    _contactMail = new QLineEdit(tr("Enter your project contact mail here..."));
    _usersRoles = new RoleTableWidget();
    _facebook = new QLineEdit(tr("Enter the project's facebook page link here..."));
    _twitter = new QLineEdit(tr("Enter the project's twitter account link here..."));
    SetWidgetsActive(false);

    //Then we build the content layouts
    _basicProjectInformations->addWidget(_logo);
    _basicProjectInformations->addRow(new QLabel(tr("Project name : ")), _projectName);
    _basicProjectInformations->addRow(new QLabel(tr("Project description : ")), _projectDesc);
    _basicProjectInformations->addRow(new QLabel(tr("Company name : ")), _companyName);
    _basicProjectInformations->addRow(new QLabel(tr("Contact phone : ")), _contactPhone);
    _basicProjectInformations->addRow(new QLabel(tr("Contact mail : ")), _contactMail);

    _socialInformations->addRow(new QLabel(tr("Facebook link : ")), _facebook);
    _socialInformations->addRow(new QLabel(tr("Twitter link : ")), _twitter);

    //We connect the events
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));

    //Finally we build all the layouts and widgets together
    _mainLayout->addWidget(_btnEditMode);
    _mainLayout->addWidget(new QLabel(tr("<h2>Basic Informations</h2>")));
    _mainLayout->addLayout(_basicProjectInformations);
    _mainLayout->addWidget(new QLabel(tr("<h2>Users and roles</h2>")));
    _mainLayout->addWidget(_usersRoles);
    _mainLayout->addWidget(new QLabel(tr("<h2>Social Informations</h2>")));
    _mainLayout->addLayout(_socialInformations);
}

BodyProjectSettings::~BodyProjectSettings()
{

}

void BodyProjectSettings::Show(int ID, MainWindow *mainApp)
{
    QVector<QString> data;

    _mainApplication = mainApp;
    _id = ID;
    data.append(API::SDataManager::GetDataManager()->GetToken());
    _api->Get(API::DP_PROJECT, API::GR_PROJECTS_USER, data, this, "GetSettingsSuccess", "Failure");

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
    _usersRoles->setEnabled(active);
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
    //Disable the button and widgets, change the connexion
    _btnEditMode->setEnabled(false);
    SetWidgetsActive(false);
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToStaticMode()));
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));


    //Then make the API call

    //Then reactivate the button
    _btnEditMode->setText(tr("Pass in edit mode"));
    _btnEditMode->setEnabled(true);
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
    emit OnLoadingDone(_id);
    dataRole.append(API::SDataManager::GetDataManager()->GetToken());
    dataRole.append(QString::number(_projectID));
    _usersRoles->Clear();
    _usersRoles->reset();
    _api->Get(API::DP_PROJECT, API::GR_PROJECT_ROLE, dataRole, this, "GetRolesSuccess", "Failure");
    _api->Get(API::DP_PROJECT, API::GR_PROJECT_USERS, dataRole, this, "GetUsersSuccess", "Failure");
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
    _usersRoles->reset();
    _usersRoles->refresh();
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
        qDebug() << "TOTOa";
    }
    _usersRoles->reset();
    _usersRoles->refresh();
}
