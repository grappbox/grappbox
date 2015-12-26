#ifndef BUGVIEWSTATUSBAR_H
#define BUGVIEWSTATUSBAR_H

#include <QWidget>
#include <QHBoxLayout>
#include <QLabel>

#define PH_BUGOPENSTATE     tr("Open")
#define PH_BUGCLOSEDSTATE   tr("Closed")
#define PH_BUGCREATORNAME   tr("Opened by")

class BugViewStatusBar : public QWidget
{
    Q_OBJECT

    enum BugState
    {
        NONE = 0,
        OPEN,
        CLOSED
    };

public:
    explicit    BugViewStatusBar(QWidget *parent = 0);
    void        SetCreatorName(const QString &name);
    void        SetBugStatus(const BugState state);

signals:

public slots:

private:
    QString     _creatorName;
    BugState    _bugState;
    QHBoxLayout *_mainLayout;
    QLabel      *_lblBugStatus;
    QLabel      *_lblCreatorName;
    QLabel      *_lblCreationDate;
};

#endif // BUGVIEWSTATUSBAR_H
