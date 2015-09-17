#ifndef DASHBOARDMEMBER_H
#define DASHBOARDMEMBER_H

#include <QWidget>
#include <QImage>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>

class DashboardMember : public QWidget
{
    Q_OBJECT
public:
    explicit DashboardMember(QWidget *parent = 0, int userId = 0);

signals:

public slots:

private:
    int             _UserId;

//Widget
private:
    // Overlay


    // Base card
    QVBoxLayout     *_MainLayout;
    QHBoxLayout     *_StateLayout;

    QImage          *_MemberPicture;
    QLabel          *_MemberPictureDrawer;
    QLabel          *_MemberName;
    QLabel          *_BusyDrawer;
    QPushButton     *_AddTaskButton;
};

#endif // DASHBOARDMEMBER_H
