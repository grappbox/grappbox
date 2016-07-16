#include "whiteboard.h"

Whiteboard::Whiteboard(QWidget *parent) :
    QWidget(parent)
{
    m_Brush = new QPainter();
    m_svgSaver = new QSvgGenerator();
    m_svgStream = new QXmlStreamReader();
    m_svgRenderer = new QSvgRenderer(m_svgStream);
    m_whiteboard = new QList<s_WhiteboardItem>();
    m_loadedSvg = new QList<QSvgRenderer *>();
    m_svgRenderer->setViewBox(this->rect());
    m_currentTool = E_LINE;

    QObject::connect(m_svgRenderer, SIGNAL(repaintNeeded()), this, SLOT(repaint()));
}

void Whiteboard::paintOnBoard(QPainter *painter, const bool isRender)
{
    QPainterPath    painterpath;
    int             i;

    painter->setBrush(QBrush(Qt::green));
    for (i = 0; i < m_loadedSvg->length(); ++i)
        m_loadedSvg->at(i)->render(painter);
    for (i = 0; i < m_whiteboard->length(); ++i)
    {
        switch(m_whiteboard->at(i).m_tool)
        {
        case E_LINE:
            painterpath.moveTo(m_whiteboard->at(i).m_pos.p1());
            painterpath.lineTo(m_whiteboard->at(i).m_pos.p2());
            break;
        case E_CIRCLE:
            painterpath.setFillRule(Qt::WindingFill);
            painterpath.addEllipse(m_whiteboard->at(i).m_pos.x1(), m_whiteboard->at(i).m_pos.y1(),m_whiteboard->at(i).m_pos.x2() - m_whiteboard->at(i).m_pos.x1(), m_whiteboard->at(i).m_pos.y2() - m_whiteboard->at(i).m_pos.y1());
            break;
        case E_SQUARE:
            painterpath.setFillRule(Qt::WindingFill);
            painterpath.addRect(m_whiteboard->at(i).m_pos.x1(), m_whiteboard->at(i).m_pos.y1(),m_whiteboard->at(i).m_pos.x2() - m_whiteboard->at(i).m_pos.x1(), m_whiteboard->at(i).m_pos.y2() - m_whiteboard->at(i).m_pos.y1());
            break;
        case E_NONE:
        default:
            continue;
            break;
        }
        painter->drawPath(painterpath);
    }
    if (isRender)
        m_svgRenderer->render(painter);
}

void Whiteboard::paintEvent(QPaintEvent *event)
{
    QPainter        painter(this);

    static_cast<void>(event);
    this->paintOnBoard(&painter);
}

void Whiteboard::mousePressEvent(QMouseEvent *evt)
{
    m_currentShapePoints.setP1(evt->pos());
}

void Whiteboard::mouseReleaseEvent(QMouseEvent *evt)
{
    m_currentShapePoints.setP2(evt->pos());
    m_whiteboard->append(s_WhiteboardItem(m_currentShapePoints, m_currentTool));
    this->repaint();
}


void    Whiteboard::setSquareTool()
{
    m_currentTool = E_SQUARE;
}

void    Whiteboard::setCircleTool()
{
    m_currentTool = E_CIRCLE;
}

void    Whiteboard::setLineTool()
{
    m_currentTool = E_LINE;
}

void    Whiteboard::loadSVG(const QString &file)
{
    m_loadedSvg->append(new QSvgRenderer(file));
}

void    Whiteboard::saveSVG(const QString &fileName)
{
    QSvgGenerator generator;
    QPainter      painter(&generator);

    generator.setFileName(fileName);
    painter.begin(&generator);
    this->paintOnBoard(&painter);
    painter.end();
}
