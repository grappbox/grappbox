#include <QDebug>

#include "MainWindow.h"

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
{

    // Basic MainWindow Configuration

    setStyleSheet("QMainWindow {background-color: #ffffff;}");
    this->layout()->setSpacing(0);
    this->setWindowTitle("Grappbox");
    this->setMinimumSize(800, 600);
    this->setContentsMargins(0, 0, 0, 0);

    // Widget creation

    QWidget *mainWidget = new QWidget();

    _MainLayout = new QVBoxLayout();
    _SliderLayout = new QVBoxLayout();
    _HeaderLayout = new QHBoxLayout();
    _ContainLayout = new QHBoxLayout();

    _MainLayout->setSpacing(0);
    _SliderLayout->setSpacing(0);
    _HeaderLayout->setSpacing(0);
    _ContainLayout->setSpacing(0);

    // Temporary things, will be replaced soon
    {
        // Temporary, will be replaced by the real Canvas system.
        _CurrentCanvas = new QPushButton("Canva : Dashboard");
        _CurrentCanvas->setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);

        // Temporary, Not implemented now.
        _ProfilWidget = new QPushButton("I'm the profil");
    }
    _MenuWidget = new SliderMenu();
    _MenuWidget->AddMenuItem("Dashboard");
    _MenuWidget->AddMenuItem("Whiteboard");

    _GrabboxNameLabel = new QLabel("Grappbox");
    _NotificationButton = new QPushButton("N");
    _AlertButton = new QPushButton("A");
    _ParameterButton = new QPushButton("P");

    _SliderLayout->addWidget(_ProfilWidget);
    _SliderLayout->addWidget(_MenuWidget);

    _HeaderLayout->addWidget(_GrabboxNameLabel, 40);
    _HeaderLayout->addWidget(_NotificationButton, 1);
    _HeaderLayout->addWidget(_AlertButton, 1);
    _HeaderLayout->addWidget(_ParameterButton, 1);

    // Layout disposition

    _MainLayout->addLayout(_HeaderLayout);
    _MainLayout->addLayout(_ContainLayout);
    _ContainLayout->addLayout(_SliderLayout, 1);
    _ContainLayout->addWidget(_CurrentCanvas, 4);
    _ContainLayout->setMargin(0);

    mainWidget->setLayout(_MainLayout);
    this->setCentralWidget(mainWidget);
    _MenuWidget->ForceChangeMenu(0);

    QObject::connect(_MenuWidget, SIGNAL(MenuChanged(int)), this, SLOT(OnMenuChange(int)));
}

MainWindow::~MainWindow()
{

}

void MainWindow::OnMenuChange(int id)
{
    _CurrentCanvas->setText("Canva : " + _MenuWidget->GetMenuItem(id));
}
