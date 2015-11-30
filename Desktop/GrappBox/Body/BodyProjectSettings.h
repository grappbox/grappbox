#ifndef BODYPROJECTSETTINGS_H
#define BODYPROJECTSETTINGS_H

#include "ibodycontener.h"
#include "Settings/ImageUploadWidget.h"
#include "Settings/RoleTableWidget.h"
#include <QVBoxLayout>
#include <QFormLayout>
#include <QPushButton>
#include <QLineEdit>
#include <QTextEdit>
#include <QPixmap>

class BodyProjectSettings: public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit            BodyProjectSettings(QWidget *parent = 0);
    virtual             ~BodyProjectSettings();

    virtual void        Show(int ID, MainWindow *mainApp);
    virtual void        Hide();

private:
    void                SetWidgetsActive(bool active);

public slots:
    void                PassToEditMode();
    void                PassToStaticMode();

signals:
    void                OnLoadingDone();

private:
    MainWindow          *_mainApplication;
    QVBoxLayout         *_mainLayout;
    QFormLayout         *_basicProjectInformations;
    QFormLayout         *_socialInformations;
    QPushButton         *_btnEditMode;

    ImageUploadWidget   *_logo;
    QLineEdit           *_projectName;
    QTextEdit           *_projectDesc;
    QLineEdit           *_contactPhone;
    QLineEdit           *_companyName;
    QLineEdit           *_contactMail;
    RoleTableWidget     *_usersRoles;
    QLineEdit           *_facebook;
    QLineEdit           *_twitter;
};

#endif // BODYPROJECTSETTINGS_H

