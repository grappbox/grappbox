#ifndef SLIDERMENU_H
#define SLIDERMENU_H

#include <QMap>
#include <QVBoxLayout>
#include <QtWidgets/QPushButton>
#include <QWidget>
#include <QScrollArea>

class SliderMenu : public QWidget
{
    Q_OBJECT
public:
    explicit SliderMenu(QWidget *parent = 0);
    void AddMenuItem(QString name, int id);
    QString GetMenuItem(int id);

signals:
    void MenuChanged(int);

public slots:
    void ForceChangeMenu(int menu);
    void ButtonChangeMenu();

private:
    QVBoxLayout         *_MainLayout;

private:
    QMap<int, QPushButton*>     _ListButton;
    int                 _CurrentIndex;
};

#endif // SLIDERMENU_H
