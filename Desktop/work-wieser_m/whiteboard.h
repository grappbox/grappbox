#ifndef WHITEBOARD_H
#define WHITEBOARD_H

#include <QWidget>
#include <QtSvg>
#include <QPainter>
#include <QLine>
#include <QList>
#include <QXmlReader>

class Whiteboard : public QWidget
{
    Q_OBJECT

    enum Tools
    {
        E_NONE = 0,
        E_LINE,
        E_CIRCLE,
        E_SQUARE
    };

    struct s_WhiteboardItem
    {
        s_WhiteboardItem(QLine pos, Tools tool)
        {
            m_pos = pos;
            m_tool = tool;
        }

        QLine   m_pos;
        Tools   m_tool;
    };

public slots:
    void                    setSquareTool();
    void                    setCircleTool();
    void                    setLineTool();
    void                    loadSVG(const QString &file);
    void                    saveSVG(const QString &file);

public:
    explicit                Whiteboard(QWidget *parent = 0);
    void                    mousePressEvent(QMouseEvent *evt);
    void                    mouseReleaseEvent(QMouseEvent *evt);
    void                    paintEvent(QPaintEvent *event);
    void                    setTool(Tools tool);

private:
    void                    paintOnBoard(QPainter *painter, const bool isRender = true);

private:
    QXmlStreamReader        *m_svgStream;
    QSvgGenerator           *m_svgSaver;
    QSvgRenderer            *m_svgRenderer;
    QPainter                *m_Brush;
    QList<s_WhiteboardItem> *m_whiteboard;
    QList<QSvgRenderer *>   *m_loadedSvg;
    Tools                   m_currentTool;
    QLine                   m_currentShapePoints;

};

#endif // WHITEBOARD_H
