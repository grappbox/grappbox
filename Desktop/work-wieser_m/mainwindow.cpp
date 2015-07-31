#include "mainwindow.h"
#include "ui_mainwindow.h"

MainWindow::MainWindow(QWidget *parent) :
    QMainWindow(parent),
    m_ui(new Ui::MainWindow)
{
    m_ui->setupUi(this);
    m_whiteboard = new Whiteboard();
    m_ui->mainLayout->addWidget(m_whiteboard);
    m_fileDialog = new QFileDialog();

    QObject::connect(m_ui->actionSquare, SIGNAL(triggered()), m_whiteboard, SLOT(setSquareTool()));
    QObject::connect(m_ui->actionCircle, SIGNAL(triggered()), m_whiteboard, SLOT(setCircleTool()));
    QObject::connect(m_ui->actionLine, SIGNAL(triggered()), m_whiteboard, SLOT(setLineTool()));
    QObject::connect(m_ui->actionOpen, SIGNAL(triggered()), this, SLOT(actionOpen()));
    QObject::connect(m_ui->actionSave, SIGNAL(triggered()), this, SLOT(actionSave()));
}

MainWindow::~MainWindow()
{
    delete m_ui;
}

void MainWindow::actionOpen()
{
    QObject::connect(m_fileDialog, SIGNAL(fileSelected(QString)), m_whiteboard, SLOT(loadSVG(QString)));
    m_fileDialog->show();
}

void MainWindow::actionSave()
{
    QObject::connect(m_fileDialog, SIGNAL(fileSelected(QString)), m_whiteboard, SLOT(saveSVG(QString)));
    m_fileDialog->setAcceptMode(QFileDialog::AcceptSave);
    m_fileDialog->show();
}
