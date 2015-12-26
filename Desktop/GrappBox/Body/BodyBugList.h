#ifndef BODYBUGLIST_H
#define BODYBUGLIST_H

#include "ibodycontener.h"
#include "BugTracker/BugListElement.h"
#include "BugTracker/BugListTitleWidget.h"
#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QScrollArea>
#include <QList>
#include <QPair>

#define UNUSED __attribute__((unused))
#define LIST_ELEM_HEIGHT    50
#define LIST_TITLE_HEIGHT   50

class BodyBugList : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit            BodyBugList(QWidget *parent = 0);
    virtual void        Show(int ID, MainWindow *mainApp);
    virtual void        Hide();

private:
    void                DeleteListElements();
    void                CreateList(QList<QPair<int, QString> > &elemList);

signals:
    void                OnLoadingDone(int);

public slots:

private:
    int                 _bodyID;
    QVBoxLayout         *_mainLayout;
    QVBoxLayout         *_listAdapter;
    BugListTitleWidget  *_title;
    QScrollArea         *_listScrollView;
    MainWindow          *_mainApp;
};

#endif // BODYBUGLIST_H
