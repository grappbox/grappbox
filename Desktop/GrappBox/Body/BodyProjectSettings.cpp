#include "Body/BodyProjectSettings.h"

BodyProjectSettings::BodyProjectSettings(QWidget *parent) : QWidget(parent)
{
    //First we create the widgets and disable them (read mode)
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

    //TODO : DELETE START
    _usersRoles->addRole(QString("Intern"), 1);
    _usersRoles->addRole(QString("Designer"), 21);
    _usersRoles->addRole(QString("Developers"), 42);
    _usersRoles->addRole(QString("Project Leader"), 36);
    _usersRoles->addRole(QString("Boss"), 100);
    _usersRoles->addUser(QString("Cathal Mc Cosker"), 21);
    _usersRoles->addUser(QString("Candy Chiu"), 42);
    _usersRoles->addUser(QString("Roland Hemmer"), 117);
    _usersRoles->addUser(QString("Allyriane Launois"), 265);
    _usersRoles->refresh();
    //TODO : DELETE END

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
    _mainApplication = mainApp;
    emit OnLoadingDone(ID);
    _id = ID;
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
