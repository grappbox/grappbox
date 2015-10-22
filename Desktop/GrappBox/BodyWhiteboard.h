#ifndef BODYWHITEBOARD_H
#define BODYWHIT

#include "WhiteboardButtonChoice.h"
#include "whiteboardcanvas.h"
#include "IBodyContener.h"
#include "WhiteboardGraphicsView.h"
#include <QWidget>
#include <QScrollArea>
#include <QPoint>
#include <QStackedLayout>
#include <QComboBox>
#include <QTableView>

#include <QMap>

class BodyWhiteboard : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit BodyWhiteboard(QWidget *parent = 0);
    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:

public slots:
    void OnActionWhiteboard(int id);

private:
    int         _ProjectId;
    int         _WhiteboardId;
    MainWindow  *_MainApplication;

    QStackedLayout *_MainLayout;
    QGridLayout *_WhiteboardChoice;
    QScrollArea *_Area;

    QVBoxLayout *_MainLayoutWhiteboard;
    WhiteboardGraphicsView   *_View;
    WhiteboardCanvas    *_Whiteboard;

    QHBoxLayout *_MenuLayout;
    QTableView *_Table;
    QComboBox *_ColorPenChoice;
    QComboBox *_ColorBackgroundChoice;
    QComboBox *_PenSizeChoice;

    QMap<int,GraphicsType>  _MapId;
};

#endif // BODYWHITEBOARD_H
