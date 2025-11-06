<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">
    <xsl:template match="/">
        <html lang="en-us">
            <head>
            </head>
            <body>
                <xsl:apply-templates select="node()|@*"/>
            </body>
        </html>
    </xsl:template>
    <xsl:template match="//subsect/designator">
        <span data-inline="num" class="{local-name()}">
            <xsl:apply-templates select="node()|@*"/>
        </span>
    </xsl:template>
    <!-- All inlines turned to spans -->
    <xsl:template match="*[local-name()='rilink'] |
                        *[local-name()='mlink'] |
                        *[local-name()='tlink'] |
                        *[local-name()='footnoteref'] |
                        *[local-name()='add'] |
                        *[local-name()='hr'] |
                        *[local-name()='linebreak'] |
                        *[local-name()='reviewdates'] |
                        *[local-name()='nbsp'] |
                        *[local-name()='subsectnote'] |
                        *[local-name()='bookmark'] |
                        *[local-name()='format'] |
                        *[local-name()='catchnote'] |
                        *[local-name()='actcitation'] |
                        *[local-name()='actseccitation'] |
                        *[local-name()='codecitation'] |
                        *[local-name()='casecitation'] |
                        *[local-name()='regcitation'] |
                        *[local-name()='ordercitation'] |
                        *[local-name()='citation'] |
                        *[local-name()='cite'] |
                        *[local-name()='subdiv'] |
                        *[local-name()='actsec'] |
                        *[local-name()='casename'] |
                        *[local-name()='legstring'] |
                        *[local-name()='docket'] |
                        *[local-name()='sessionyrtx'] |
                        *[local-name()='contingencytext'] |
                        *[local-name()='renumberto'] |
                        *[local-name()='renumberfrom'] |
                        *[local-name()='textdate'] |
                        *[local-name()='effectivedate'] |
                        *[local-name()='expirationdate'] |
                        *[local-name()='operationaldate'] |
                        *[local-name()='terminationdate'] |
                        *[local-name()='citeas'] |
                        *[local-name()='sessionyear'] |
                        *[local-name()='sessionyearshort'] |
                        *[local-name()='codeabbrev'] |
                        *[local-name()='codesec'] |
                        *[local-name()='contingencytext'] |
                        *[local-name()='renumberto'] |
                        *[local-name()='renumberfrom'] |
                        *[local-name()='reviewdate'] |
                        *[local-name()='codesec_1_n'] |
                        *[local-name()='codesec_2_n'] |
                        *[local-name()='codesec_3_y'] |
                        *[local-name()='actnum'] |
                        *[local-name()='actid'] |
                        *[local-name()='contingencytext'] |
                        *[local-name()='primaryidcodenumber'] |
                        *[local-name()='primaryidcodename'] |
                        *[local-name()='partnumber'] |
                        *[local-name()='partname'] |
                        *[local-name()='chapternumber'] |
                        *[local-name()='chaptername'] |
                        *[local-name()='rulenumber'] |
                        *[local-name()='primaryidcodename'] |
                        *[local-name()='versioncount'] |
                        *[local-name()='notetext'] |
                        *[local-name()='multiversioncitation'] |
                        *[local-name()='drop'] |
                        *[local-name()='dropreplace'] |
                        *[local-name()='filedate'] |
                        *[local-name()='PreviousAct'] |
                        *[local-name()='NewEffectiveDate'] |
                        *[local-name()='PreviousEffectiveDate']">
        <span data-inline="{local-name()}" data-element="{local-name()}">
            <xsl:apply-templates select="node()|@*"/>
        </span>
    </xsl:template>
    <!-- <xsl:template match="*[local-name()='bold']">
        <strong>
            <xsl:apply-templates select="node()|@*"/>
        </strong>
    </xsl:template> -->
    <xsl:template match="*[local-name()='italic']">
        <em>
            <xsl:apply-templates select="node()|@*"/>
        </em>
    </xsl:template>
    <xsl:template match="*[local-name()='monospace']">
        <code>
            <xsl:apply-templates select="node()|@*"/>
        </code>
    </xsl:template>
    <xsl:template match="*[local-name()='underscore']">
        <u>
            <xsl:apply-templates select="node()|@*"/>
        </u>
    </xsl:template>
    <xsl:template match="*[local-name()='subscript']">
        <sub>
            <xsl:apply-templates select="node()|@*"/>
        </sub>
    </xsl:template>
    <xsl:template match="*[local-name()='superscript']">
        <sup>
            <xsl:apply-templates select="node()|@*"/>
        </sup>
    </xsl:template>
    <xsl:template match="*[local-name()='del']">
        <del>
            <xsl:apply-templates select="node()|@*"/>
        </del>
    </xsl:template>
    <xsl:template match="*[local-name()='strike']">
        <del>
            <xsl:apply-templates select="node()|@*"/>
        </del>
    </xsl:template>
    <!-- Everything to divs -->
    <xsl:template match="*">
        <div data-element="{local-name()}">
            <xsl:apply-templates select="node()|@*"/>
        </div>
    </xsl:template>
    <xsl:template match="*[local-name()='ul'] |
                *[local-name()='li'] |
                *[local-name()='TABLE'] |
                *[local-name()='CAPTION'] |
                *[local-name()='THEAD'] |
                *[local-name()='TFOOT'] |
                *[local-name()='TBODY'] |
                *[local-name()='COLGROUP'] |
                *[local-name()='COL'] |
                *[local-name()='TR'] |
                *[local-name()='TH'] |
                *[local-name()='TD']">
        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="node()|@*"/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="*[local-name()='linebreak']">
        <br/>
    </xsl:template>
    <xsl:template match="*[local-name()='hr']">
        <hr/>
    </xsl:template>
    <xsl:template match="*[local-name()='ulink'] | *[local-name()='bookmark']">
        <a href="{@url}" data-element="ulink">
            <xsl:apply-templates select="node()|@*"/>
        </a>
    </xsl:template>
    <xsl:template match="*[local-name()='filelink']">
        <a href="https://img.norma.com/cmdata/{@filename}" _target="blank" data-element="filelink">
            <xsl:apply-templates select="node()|@*"/>
        </a>
    </xsl:template>
    <xsl:template match="*[local-name()='para'] | *[local-name()='line'] | *[local-name()='intro']">
        <p data-element="{local-name()}">
            <xsl:apply-templates select="node()|@*"/>
        </p>
    </xsl:template>
    <xsl:template match="*[local-name()='notes'] | *[local-name()='footer']">
        <div data-type="note" data-element="{local-name()}">
            <xsl:attribute name="style">
                <xsl:value-of select="'font-style: italic; background: #EEE; padding: 10px;'"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </div>
    </xsl:template>
    <xsl:template match="*[local-name()='code']">
        <div data-element="{local-name()}">
            <xsl:attribute name="style">
                <xsl:value-of select="'padding-top: 15px;'"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </div>
    </xsl:template>
    <xsl:template match="*[local-name()='subsect']">
        <div data-element="{local-name()}">
            <xsl:attribute name="style">
                <xsl:value-of select="'margin-left: 30px; margin-top: 7px; margin-bottom: 7px;'"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </div>
    </xsl:template>
    <!-- Remove codesource attributes -->
    <xsl:template match="@codesource"/>
    <!-- Remove source attributes -->
    <xsl:template match="@source"/>
    <!-- Remove version elements -->
    <xsl:template match="version"/>
    <!-- Remove currency elements -->
    <xsl:template match="*[local-name()='currency']"/>
    <!-- Remove data-id attribute that we added -->
    <xsl:template match="@data-id" />
    <xsl:template match="@*">
        <xsl:if test="name() != 'class'">
            <!-- <xsl:copy-of select="."/> -->
            <xsl:attribute name="data-{local-name()}">
                <xsl:value-of select="."/>
            </xsl:attribute>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
