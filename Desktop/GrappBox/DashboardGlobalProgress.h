#ifndef DASHBOARDGLOBALPROGRESS_H
#define DASHBOARDGLOBALPROGRESS_H

#include <QWidget>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QLabel>
#include <QImage>

class DashboardGlobalProgress : public QWidget
{
    Q_OBJECT
public:
    explicit DashboardGlobalProgress(QWidget *parent = 0);

signals:

public slots:

    // Widgets
private:
    // Layout
    QVBoxLayout             *_MainLayout;
    QHBoxLayout             *_ProjectInfoLayout;
    QVBoxLayout             *_TextProjectInfoLayout;
    QHBoxLayout             *_ProgressInfoLayout;

    // Project info
    QLabel                  *_ProjectPicture;
    QLabel                  *_ProjectTitle;
    QLabel                  *_ProjectCompany;
    QLabel                  *_ProjectTel;
    QLabel                  *_ProjectMail;

    // Progress info
    QLabel                  *_NumberOfTask;
    QLabel                  *_NumberOfMsg;
    QLabel                  *_NumberOfProblem;
};

#endif // DASHBOARDGLOBALPROGRESS_H
