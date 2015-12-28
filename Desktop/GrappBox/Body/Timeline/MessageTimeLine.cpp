#include <QDebug>
#include "SFontLoader.h"
#include "MessageTimeLine.h"

MessageTimeLine::MessageTimeLine(int idTimeline, QWidget *parent) : QWidget(parent)
{
    _IDTimeline = idTimeline;
    _IDUserCreator = API::SDataManager::GetDataManager()->GetUserId();

    bool canEdit = _IDUserCreator == API::SDataManager::GetDataManager()->GetUserId();

    QWidget *normalModeWidget = new QWidget(this);
    QWidget *editModeWidget = new QWidget(this);

    _MainLayout = new QStackedLayout();
    _MainLayoutNormal = new QGridLayout();
    _MainLayoutEdit = new QVBoxLayout();
    _ButtonLayout = new QHBoxLayout();

    _Avatar = new QLabel();
    _Avatar->setPixmap(QPixmap(":/Image/Ressources/Icon/UserDefault.png").scaled(QSize(64, 64)));
    _Avatar->setFixedSize(64, 64);
    _Title = new QLabel("Titre");
    _Title->setFixedHeight(30);
    _Title->setStyleSheet("background: #2abb67; color: #FFFFFF; border-style:none; border-bottom-style: solid; border-width: 1px; border-color: #FFFFFF; ");
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPixelSize(20);
    _Title->setFont(font);
    _Message = new QLabel("Ceci est un message.");
    _Message->setStyleSheet("QLabel {border-style:none; border-bottom-style: solid; border-width: 1px; border-color: #5a5a5a; }");
    _Date = new QLabel("Last modified : 09/04/2013 05:24PM by Leo Nadeau");
    _Date->setFixedHeight(24);
    _EditButton = new PushButtonImage();
    _EditButton->SetImage(QPixmap(":/icon/Ressources/Icon/Edit.png"));
    _EditButton->setFixedSize(24, 24);
    _RemoveButton = new PushButtonImage();
    _RemoveButton->SetImage(QPixmap(":/icon/Ressources/Icon/Delete.png"));
    _RemoveButton->setFixedSize(24, 24);
    _EditButton->setDisabled(!canEdit);
    _RemoveButton->setDisabled(!canEdit);

    _TitleEdit = new QLineEdit();
    _EditMessageArea = new QTextEdit();
    _ValidateButton = new PushButtonImage();
    _ValidateButton->SetImage(QPixmap(":/icon/Ressources/Icon/Accept.png"));
    _ValidateButton->setFixedSize(24, 24);
    _CancelButton = new PushButtonImage();
    _CancelButton->SetImage(QPixmap(":/icon/Ressources/Icon/Return.png"));
    _CancelButton->setFixedSize(24, 24);

    _ButtonLayout->addWidget(_ValidateButton);
    _ButtonLayout->addWidget(_CancelButton);
    _MainLayoutEdit->addWidget(_TitleEdit);
    if (_IDTimeline != 1) // Remplacer par : Si ce n'est pas un commentaire
        _TitleEdit->hide();
    _MainLayoutEdit->addWidget(_EditMessageArea);
    _MainLayoutEdit->addLayout(_ButtonLayout);

    editModeWidget->setLayout(_MainLayoutEdit);
    if (_IDTimeline == 1) // Remplacer par : Si ce n'est pas un commentaire
        _MainLayoutNormal->addWidget(_Title, 0, 0, 1, 6);
    _MainLayoutNormal->addWidget(_Avatar, 1, 0, 3, 1);
    _MainLayoutNormal->addWidget(_Message, 1, 1, 2, 5);
    _MainLayoutNormal->addWidget(_Date, 3, 1, 1, 3);
    _MainLayoutNormal->addWidget(_EditButton, 3, 4, 1, 1);
    _MainLayoutNormal->addWidget(_RemoveButton, 3, 5, 1, 1);
    _MainLayoutNormal->setSpacing(0);
    _MainLayoutNormal->setContentsMargins(0, 0, 0, 0);

    normalModeWidget->setLayout(_MainLayoutNormal);

    _IDLayoutNormal = _MainLayout->addWidget(normalModeWidget);
    _IDLayoutEdit = _MainLayout->addWidget(editModeWidget);

    setLayout(_MainLayout);
    setStyleSheet("background-color: #FFFFFF;");
    setContentsMargins(0, 0, 0, 0);

    QObject::connect(_EditButton, SIGNAL(clicked(bool)), this, SLOT(OnEdit()));
    QObject::connect(_RemoveButton, SIGNAL(clicked(bool)), this, SLOT(OnRemove()));
    QObject::connect(_CancelButton, SIGNAL(clicked(bool)), this, SLOT(OnCancelEdit()));
    QObject::connect(_ValidateButton, SIGNAL(clicked(bool)), this, SLOT(OnConfirmEdit()));
}

void MessageTimeLine::OnEdit()
{
    _TitleEdit->setText(_Title->text());
    _EditMessageArea->setText(_Message->text());
    _MainLayout->setCurrentIndex(_IDLayoutEdit);
}

void MessageTimeLine::OnRemove()
{
    // Here remove the message
    qDebug() << _EditButton->size();
    qDebug() << _RemoveButton->size();
    emit TimelineDeleted(_IDTimeline);
}

void MessageTimeLine::OnCancelEdit()
{
    _MainLayout->setCurrentIndex(_IDLayoutNormal);
}

void MessageTimeLine::OnConfirmEdit()
{
    // Here do the edit
    emit TimelineEdited(_IDTimeline);
    _MainLayout->setCurrentIndex(_IDLayoutNormal);
}
