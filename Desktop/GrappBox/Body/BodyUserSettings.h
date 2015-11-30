#ifndef BODYUSERSETTINGS
#define BODYUSERSETTINGS

#include "ibodycontener.h"
#include "Settings/ImageUploadWidget.h"
#include "API/SDataManager.h"
#include <QVBoxLayout>
#include <QFormLayout>
#include <QLineEdit>
#include <QPushButton>
#include <QDateTimeEdit>
#include <QComboBox>
#include <QLocale>
#include <QDebug>

#define UNUSED __attribute__((unused))
#define PH_FIRSTNAME "Enter your firstname here"
#define PH_LASTNAME "Enter your lastname here"
#define PH_EMAIL "Enter your email here"
#define PH_PHONE "Enter your phone number here"
#define PH_LINKEDIN "Enter your linkedin public URL here"
#define PH_VIADEO "Enter your viadeo public URL here"
#define PH_TWITTER "Enter your twitter public URL here"

class BodyUserSettings : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit            BodyUserSettings(QWidget *parent = 0);
    virtual             ~BodyUserSettings();

    virtual void        Show(int ID, MainWindow *mainApp);
    virtual void        Hide();

private:
    void                SetWidgetActiveState(bool active);

public slots:
    void                PassToEditMode();
    void                PassToStaticMode();
    void                Failure(int, QByteArray);
    void                GetUserData(int, QByteArray);
    void                SaveUserData(int, QByteArray);

signals:
    void                OnLoadingDone(int);

private:

    MainWindow          *_mainApplication;
    QVBoxLayout         *_mainLayout;
    QFormLayout         *_personalInformationLayout;
    QFormLayout         *_socialInformationLayout;
    QFormLayout         *_newPassword;
    QPushButton         *_btnEditMode;

    ImageUploadWidget   *_avatar;
    QLineEdit           *_firstname;
    QLineEdit           *_lastname;
    QDateTimeEdit       *_birthday;
    QLineEdit           *_email;
    QLineEdit           *_phone;
    QComboBox           *_country;

    QLineEdit           *_linkedin;
    QLineEdit           *_viadeo;
    QLineEdit           *_twitter;

    QLineEdit           *_password;
    QLineEdit           *_confirmPassword;

    API::IDataConnector *_dataManager;
    int                 _id;
};

#endif // BODYUSERSETTINGS

