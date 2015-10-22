#include "ProfilMainInformation.h"
#include "SFontLoader.h"

ProfilMainInformation::ProfilMainInformation(int idUser, QWidget *parent) : QWidget(parent)
{
    _CurrentIdUser = idUser;

    _MainLayout = new QVBoxLayout();
    _ProgressLayout = new QHBoxLayout();
    _ProgressCounterLayout = new QVBoxLayout();
    _ButtonLayout = new QHBoxLayout();

    _MainLayout->setSpacing(0);
    _MainLayout->setMargin(0);
    _ProgressLayout->setSpacing(0);
    _ProgressLayout->setMargin(0);
    _ProgressCounterLayout->setSpacing(0);
    _ProgressCounterLayout->setMargin(0);
    _ButtonLayout->setSpacing(0);
    _ButtonLayout->setMargin(0);

    setLayout(_MainLayout);

    _FixedLabelProgress = new QLabel("Your progress");
    _RealLabelProgress = new QLabel("70%");
    _ProfilPicture = new QLabel();
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(12);
    font.setBold(true);
    _FixedLabelProgress->setFont(font);
    _FixedLabelProgress->setAlignment(Qt::AlignBottom | Qt::AlignHCenter);
    _FixedLabelProgress->setStyleSheet("QLabel { color: #ffffff; }");
    font.setPointSize(24);
    _RealLabelProgress->setFont(font);
    _RealLabelProgress->setAlignment(Qt::AlignHCenter | Qt::AlignTop);
    _RealLabelProgress->setStyleSheet("QLabel { color: #ffffff; }");

    _SettingsButton = new QPushButton();
    _ProfilButton = new QPushButton();
    _LogoutButton = new QPushButton();

    _SettingsButton->setFixedHeight(42);
    _ProfilButton->setFixedHeight(42);
    _LogoutButton->setFixedHeight(42);


    _SettingsButton->setStyleSheet("QPushButton {background-color: #2d2f31;"
                                   "background-image: url(:/icon/Ressources/Icon/SettingsIcon.png);"
                                   "background-repeat: no-repeat;"
                                   "background-position: center center;}"
                                   "QPushButton:hover {background-color: #3a3d40;}"
                                   "QPushButton:pressed {background-color: #757b80;}");
    _ProfilButton->setStyleSheet("QPushButton {background-color: #2d2f31;"
                                 "background-image: url(:/icon/Ressources/Icon/V-CardIcon.png);"
                                 "background-repeat: no-repeat;"
                                 "background-position: center center;}"
                                 "QPushButton:hover {background-color: #3a3d40;}"
                                 "QPushButton:pressed {background-color: #757b80;}");
    _LogoutButton->setStyleSheet("QPushButton {background-color: #2d2f31;"
                                 "background-image: url(:/icon/Ressources/Icon/Logout-Icon.png);"
                                 "background-repeat: no-repeat;"
                                 "background-position: center center;}"
                                 "QPushButton:hover {background-color: #3a3d40;}"
                                 "QPushButton:pressed {background-color: #757b80;}");

    _MainLayout->addLayout(_ProgressLayout);
    _MainLayout->addLayout(_ButtonLayout);
    _ProgressLayout->addLayout(_ProgressCounterLayout);
    _ProgressLayout->addWidget(_ProfilPicture);
    _ProgressCounterLayout->addWidget(_FixedLabelProgress);
    _ProgressCounterLayout->addWidget(_RealLabelProgress);
    _ButtonLayout->addWidget(_SettingsButton);
    _ButtonLayout->addWidget(_ProfilButton);
    _ButtonLayout->addWidget(_LogoutButton);

    QPixmap tmpPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/marc_wieser.jpeg");
    tmpPixmap = tmpPixmap.scaled(130, 130);
    tmpPixmap.setMask(QBitmap::fromImage(QImage(":/Mask/Ressources/Mask/CircleMaskMemberPicture.png").createAlphaMask(Qt::MonoOnly)));

    _ProfilPicture->setPixmap(tmpPixmap);
    this->setStyleSheet("background: #705b5c;");
}

void ProfilMainInformation::Update(int newIdUser)
{
    if (newIdUser >= 0)
        _CurrentIdUser = newIdUser;
}
