#ifndef PROFILMAININFORMATION_H
#define PROFILMAININFORMATION_H

#include <QtWidgets/QWidget>
#include <QtWidgets/QLabel>
#include <QImage>
#include <QtWidgets/QPushButton>
#include <QBitmap>
#include <QPixmap>

#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QHBoxLayout>

class ProfilMainInformation : public QWidget
{
    Q_OBJECT
public:
    explicit ProfilMainInformation(QObject *mainWindow, QWidget *parent = 0);

    void Update(int newIdUser = -1);

signals:
    void OnMainSettings();
    void OnUserSettings();

public slots:

private:
    QVBoxLayout *_MainLayout;
    QHBoxLayout *_ProgressLayout;
    QVBoxLayout *_ProgressCounterLayout;
    QHBoxLayout *_ButtonLayout;

    QLabel      *_FixedLabelProgress;
    QLabel      *_RealLabelProgress;
    QLabel      *_ProfilPicture;
    QPushButton *_SettingsButton;
    QPushButton *_ProfilButton;
    QPushButton *_LogoutButton;

    int         _CurrentIdUser;
};

#endif // PROFILMAININFORMATION_H
