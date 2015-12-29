#ifndef BODYTIMELINE_H
#define BODYTIMELINE_H

#include "IBodyContener.h"
#include "Body/Timeline/CanvasTimeline.h"

#include <QStackedLayout>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QScrollArea>

#include <QWidget>
#include <QPushButton>

class BodyTimeline : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit BodyTimeline(QWidget *parent = 0);
    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:
    void OnLoadingDone(int);

public slots:
    void OnChange();

private:
    QVBoxLayout         *_MainLayout;
    QStackedLayout      *_MainLayoutTimeline;
    QHBoxLayout         *_MainLayoutButton;

    int                 _IDButtonClient;
    int                 _IDButtonTeam;

    QPushButton         *_ButtonToClient;
    QPushButton         *_ButtonToTeam;

    QScrollArea         *_ClientSA;
    QScrollArea         *_TeamSA;

    CanvasTimeline      *_ClientTimeline;
    CanvasTimeline      *_TeamTimeline;

};

#endif // BODYTIMELINE_H
