#ifndef BODYUSERSETTINGS
#define BODYUSERSETTINGS

#include "ibodycontener.h"
#include "Settings/ImageUploadWidget.h"
#include <QVBoxLayout>
#include <QFormLayout>
#include <QLineEdit>
#include <QPushButton>
#include <QDateTimeEdit>
#include <QComboBox>
#include <QLocale>

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

signals:
    void                OnLoadingDone();

private:

    MainWindow          *_mainApplication;
    QVBoxLayout         *_mainLayout;
    QFormLayout         *_personalInformationLayout;
    QFormLayout         *_socialInformationLayout;
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
};

#endif // BODYUSERSETTINGS

