#ifndef BODYPROJECTSETTINGS_H
#define BODYPROJECTSETTINGS_H

#include "ibodycontener.h"
#include "Settings/ImageUploadWidget.h"
#include "Settings/RoleTableWidget.h"
#include "Settings/CustomerAccessSettings.h"
#include "Settings/CreateNewCustomerAccessWindow.h"
#include "SDataManager.h"
#include <QVBoxLayout>
#include <QFormLayout>
#include <QPushButton>
#include <QLineEdit>
#include <QTextEdit>
#include <QPixmap>
#include <QDebug>
#include <QList>
#include <QScrollArea>

#define PH_PROJECT_NAME     tr("Enter your project name here...")
#define PH_PROJECT_DESC     tr("Enter your project description here...")
#define PH_COMPANY_NAME     tr("Enter the company who order the project...")
#define PH_PROJECT_PHONE    tr("Enter the company phone here...")
#define PH_PROJECT_MAIL     tr("Enter your project contact mail here...")
#define PH_PROJECT_FACEBOOK tr("Enter the project's facebook page link here...")
#define PH_PROJECT_TWITTER  tr("Enter the project's twitter account link here...")

class BodyProjectSettings: public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit                        BodyProjectSettings(QWidget *parent = 0);
    virtual                         ~BodyProjectSettings();

    virtual void                    Show(int ID, MainWindow *mainApp);
    virtual void                    Hide();

private:
    void                            SetWidgetsActive(bool active);

public slots:
    void                            PassToEditMode();
    void                            PassToStaticMode();
    void                            DeleteProject();
    void                            RetreiveProject();
    void                            Failure(int, QByteArray);
    void                            GetSettingsSuccess(int, QByteArray);
    void                            GetRolesSuccess(int, QByteArray);
    void                            GetUsersSuccess(int, QByteArray);
    void                            SetProjectSuccess(int, QByteArray);
    void                            FailureGetSettings(int id, QByteArray data);
    void                            DeleteProjectSuccess(int id, QByteArray data);
    void                            RetreiveProjectSuccess(int id, QByteArray data);
    void                            GetCustomerAccessSuccess(int id, QByteArray data);
    void                            CustomerAccessDeleted(CustomerAccessSettings*);
    void                            AccessCreated(QString accessName);
    void                            AccessCreatedSuccess(int id, QByteArray data);
    void                            GetOneCustomerAccessSuccess(int id, QByteArray data);
    void                            GetOneCustomerAccessFailure(int id, QByteArray data);

signals:
    void                            OnLoadingDone(int);

private:
    MainWindow                      *_mainApplication;
    QVBoxLayout                     *_mainLayout;
    QVBoxLayout                     *_secondaryLayout;
    QHBoxLayout                     *_windowLayout;
    QFormLayout                     *_basicProjectInformations;
    QFormLayout                     *_socialInformations;
    QFormLayout                     *_passwordInformations;
    QHBoxLayout                     *_deleteRetreiveProject;
    QPushButton                     *_btnEditMode;

    ImageUploadWidget               *_logo;
    QLineEdit                       *_projectName;
    QTextEdit                       *_projectDesc;
    QLineEdit                       *_contactPhone;
    QLineEdit                       *_companyName;
    QLineEdit                       *_contactMail;
    RoleTableWidget                 *_usersRoles;
    QLineEdit                       *_facebook;
    QLineEdit                       *_twitter;
    QLineEdit                       *_password;
    QLineEdit                       *_passwordConfirmation;
    QPushButton                     *_deleteProject;
    QPushButton                     *_cancelDeletion;
    QVBoxLayout                     *_customerAccess;
    QPushButton                     *_createAccess;
    CreateNewCustomerAccessWindow   *_createAccessWindow;
    QList<int>                      _stackAccessID;


    int                             _id;
    int                             _projectID;
    API::IDataConnector             *_api;
    int                             _roleRequestNb;
};

#endif // BODYPROJECTSETTINGS_H

