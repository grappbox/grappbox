#ifndef PROFILMAININFORMATION_H
#define PROFILMAININFORMATION_H

#include <QWidget>
#include <QLabel>
#include <QImage>
#include <QPushButton>
#include <QBitmap>
#include <QPixmap>

#include <QVBoxLayout>
#include <QHBoxLayout>

class ProfilMainInformation : public QWidget
{
    Q_OBJECT
public:
    explicit ProfilMainInformation(int idUser, QWidget *parent = 0);

    void Update(int newIdUser = -1);

signals:

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
