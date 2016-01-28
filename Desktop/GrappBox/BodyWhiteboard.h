#ifndef BODYWHITEBOARD_H
#define BODYWHITEBOARD_H

#include "WhiteboardButtonChoice.h"
#include "whiteboardcanvas.h"
#include "IBodyContener.h"
#include "WhiteboardGraphicsView.h"
#include <QtWidgets/QWidget>
#include <QtWidgets/QScrollArea>
#include <QPoint>
#include <QtWidgets/QStackedLayout>
#include <QtWidgets/QComboBox>
#include <QtWidgets/QTableView>

#include <QMap>

class BodyWhiteboard : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit BodyWhiteboard(QWidget *parent = 0);
    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:
    void OnLoadingDone(int);

public slots:
    void OnQuitWhiteboard();
    void OnEditWhiteboard(int id);
    void OnActionWhiteboard(int id);
    void OnColorPenChange();
    void OnColorBackgroudChange();
    void OnPenSizeChange(int index);

private:
    void InitializeComboBox();
    void InitializeColorPen();
    void InitializeBackground();
    void InitializePenWidth();

private:
    int         _ProjectId;
    int         _WhiteboardId;
    MainWindow  *_MainApplication;

    QStackedLayout *_MainLayout;
    QVBoxLayout *_WhiteboardChoice;
    QScrollArea *_Area;

    QVBoxLayout *_MainLayoutWhiteboard;
    WhiteboardGraphicsView   *_View;
    WhiteboardCanvas    *_Whiteboard;

    QHBoxLayout *_MenuLayout;
    QTableView *_TableColorPen;
    QTableView *_TableBackgroud;
    QComboBox *_ColorPenChoice;
    QComboBox *_ColorBackgroundChoice;
    QComboBox *_PenSizeChoice;
    QList<QPair<QString, QString> > _HexaList;

    QMap<int,GraphicsType>  _MapId;
};

#endif // BODYWHITEBOARD_H
